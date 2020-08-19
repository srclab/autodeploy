<?php

namespace SrcLab\AutoDeploy;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Throwable;

class Deploy implements DeployInterface
{
    /**
     * @var string
     */
    protected $local_token;

    /**
     * @var string
     */
    protected $pulling_branch;

    /**
     * @var string
     */
    protected $work_dir;

    /**
     * Deploy constructor.
     *
     * @param string $local_token
     * @param string $pulling_branch
     * @param string $work_dir
     */
    public function __construct($local_token, $pulling_branch, $work_dir)
    {
        $this->local_token = $local_token;
        $this->pulling_branch = $pulling_branch;
        $this->work_dir = $work_dir;
    }

    /**
     * Деплой.
     *
     * @param string $github_payload
     * @param string $github_hash
     * @param string $github_event
     * @param string $allowed_label
     * @param int $deploy_type
     * @return bool
     * @throws ProcessFailedException|Throwable
     */
    public function deploy(
        $github_payload,
        $github_hash,
        $github_event,
        $allowed_label = '',
        $deploy_type = DeployInterface::TYPE_LARAVEL
    ) {
        $local_hash = $this->getLocalHash($github_payload, $this->local_token);

        if (! hash_equals($github_hash, $local_hash)) {
            throw new DeployException('Хэши не совпадают');
        }

        if (
            ! $this->isActualPullRequest($github_event, $github_payload, $allowed_label, $this->pulling_branch)
            && ! $this->isActualPush($github_event, $github_payload, $this->pulling_branch)
        ) {
            return false;
        }

        try {
            $processes = $this->getCommonProcesses();

            if ($deploy_type == DeployInterface::TYPE_LARAVEL) {
                $processes = array_merge($processes, $this->getLaravelProcesses());
            }

            foreach ($processes as $process) {
                $process->setWorkingDirectory($this->work_dir);
                $process->mustRun();
            }

            return true;
        } catch (Throwable $e) {
            throw new DeployException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Локальный хэш.
     *
     * @param string $github_payload
     * @param string $local_token
     * @return string
     */
    protected function getLocalHash($github_payload, $local_token)
    {
        return 'sha1=' . hash_hmac('sha1', $github_payload, $local_token, false);
    }

    /**
     * Ларавель процессы.
     *
     * @return Process[]
     */
    protected function getLaravelProcesses()
    {
        return [
            new Process(['yarn', 'run', 'prod']),
            new Process(['php', 'artisan', 'migrate' , '--force']),
            new Process(['php', 'artisan', 'cache:clear']),
            new Process(['php', 'artisan', 'config:cache']),
            new Process(['php', 'artisan', 'route:cache']),
            new Process(['php', 'artisan', 'view:cache']),
        ];
    }

    /**
     * Общие процессы.
     *
     * @return Process[]
     */
    protected function getCommonProcesses()
    {
        return [
            new Process(['git', 'pull', "origin/{$this->pulling_branch}"]),
            new Process(['composer', 'install', '--no-interaction', '--no-dev',
                '--prefer-dist', '--no-autoloader']),
            new Process(['composer', 'dump-autoload']),
            new Process(['yarn', 'install', '--production']),
        ];
    }

    /**
     * Это мерж пула в актуальную ветку.
     *
     * @param string $github_event
     * @param string $github_payload
     * @param string $allowed_label
     * @param string $pulling_branch
     * @return bool
     */
    protected function isActualPullRequest($github_event, $github_payload, $allowed_label, $pulling_branch)
    {
        if ($github_event !== 'pull_request') {
            return false;
        }

        $github_payload = json_decode($github_payload);

        return $github_payload->action === 'closed'
            && $github_payload->pull_request->merged
            && $github_payload->pull_request->base->ref === $pulling_branch
            && $this->deployAllowed($github_payload, $allowed_label);
    }

    /**
     * Деплой разрешен.
     *
     * @param object $github_payload
     * @param string $allowed_label
     * @return bool
     */
    protected function deployAllowed($github_payload, $allowed_label)
    {
        $github_labels = $github_payload->pull_request->labels;

        return array_reduce($github_labels, function ($allowed, $github_label) use ($allowed_label) {
            if ($allowed) {
                return $allowed;
            }

            return $github_label->name === $allowed_label;
        }, false);
    }

    /**
     * Это пуш в актуальную ветку.
     *
     * @param string $github_event
     * @param string $github_payload
     * @param string $pulling_branch
     * @return bool
     */
    protected function isActualPush($github_event, $github_payload, $pulling_branch)
    {
        if ($github_event !== 'push') {
            return false;
        }

        $github_payload = json_decode($github_payload);

        $branch_to_push = array_reverse(explode('/', $github_payload->ref))[0];

        return $branch_to_push === $pulling_branch;
    }
}
