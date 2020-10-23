<?php

namespace SrcLab\AutoDeploy;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Throwable;

class Deploy
{
    /**
     * Тип делоя.
     *
     * @var integer
     */
    public const NATIVE_TYPE = 1; //нативное приложение
    public const LARAVEL_TYPE = 2; //laravel приложение

    /**
     * Метка автодеплоя в пул запросе, при которой выполнять автоматические действия.
     */
    protected const DEPLOY_LABEL = 'Автодеплой';
    
    /**
     * @var array
     */
    protected $config;

    /**
     * Deploy constructor.
     *
     * @param string $local_token
     * @param string $pulling_branch
     * @param string $work_dir
     */
    public function __construct(array $config)
    {
        if(empty($config['local_token']) || empty($config['pulling_branch']) || empty($config['work_dir'])) {
            throw new DeployException('Не установлены обязательные параметры конфигурации.');
        }

        $this->config = $config;
    }

    /**
     * Деплой.
     *
     * @param string $github_payload
     * @param string $github_hash
     * @param string $github_event
     * @param int $deploy_type
     * @return bool
     * @throws ProcessFailedException|Throwable
     */
    public function deploy($github_payload, $github_hash, $github_event, $deploy_type = self::LARAVEL_TYPE)
    {

        if (!hash_equals($github_hash, $this->getLocalHash($github_payload))) {
            throw new DeployException('Хэши не совпадают');
        }

        if (
            !$this->isTargetBranchPullRequest($github_event, $github_payload)
            && !$this->isTargetBranchPush($github_event, $github_payload)
        ) {
            return false;
        }

        try {

            $processes = $this->getCommonProcesses();

            if ($deploy_type == self::LARAVEL_TYPE) {
                $processes = array_merge($processes, $this->getLaravelProcesses());
            }

            foreach ($processes as $process) {
                $process->setWorkingDirectory($this->config['work_dir']);
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
     * @return string
     */
    protected function getLocalHash($github_payload)
    {
        return 'sha1=' . hash_hmac('sha1', $github_payload, $this->config['local_token'], false);
    }

    /**
     * Общие процессы.
     *
     * @return Process[]
     */
    protected function getCommonProcesses()
    {
        return [
            new Process(['git', 'pull', "origin/{$this->config['pulling_branch']}"]),
            new Process(['composer', 'install', '--no-interaction', '--no-dev', '--prefer-dist', '--no-autoloader']),
            new Process(['composer', 'dump-autoload']),
            new Process(['yarn', 'install', '--production']),
        ];
    }

    /**
     * Laravel процессы.
     *
     * @return Process[]
     */
    protected function getLaravelProcesses()
    {
        return [
            new Process(['yarn', 'run', 'prod']),
            new Process(['php', 'artisan', 'migrate' , '--force']),
            new Process(['php', 'artisan', 'clear']),
            new Process(['php', 'artisan', 'cache:clear']),
            new Process(['php', 'artisan', 'config:cache']),
            new Process(['php', 'artisan', 'route:cache']),
            new Process(['php', 'artisan', 'view:cache']),
        ];
    }

    /**
     * Это мерж пула в целевую ветку.
     *
     * @param string $github_event
     * @param string $github_payload
     * @return bool
     */
    protected function isTargetBranchPullRequest($github_event, $github_payload)
    {
        if ($github_event !== 'pull_request') {
            return false;
        }

        $github_payload = json_decode($github_payload);

        return $github_payload->action === 'closed'
            && $github_payload->pull_request->merged
            && $github_payload->pull_request->base->ref === $this->config['pulling_branch']
            && $this->deployAllowed($github_payload);
    }

    /**
     * Деплой разрешен.
     *
     * @param object $github_payload
     * @return bool
     */
    protected function deployAllowed($github_payload)
    {
        foreach ($github_payload->pull_request->labels as $github_label) {
            if($github_label->name === self::DEPLOY_LABEL) {
                return true;
            }
        }

        return false;
    }

    /**
     * Это пуш в целевую ветку.
     *
     * @param string $github_event
     * @param string $github_payload
     * @return bool
     */
    protected function isTargetBranchPush($github_event, $github_payload)
    {
        if ($github_event !== 'push') {
            return false;
        }

        $github_payload = json_decode($github_payload);

        $branch_to_push = array_reverse(explode('/', $github_payload->ref))[0];

        return $branch_to_push === $this->config['pulling_branch'];
    }
}
