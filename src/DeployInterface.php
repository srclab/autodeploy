<?php

namespace SrcLab\AutoDeploy;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Throwable;

interface DeployInterface
{
    public const TYPE_NATIVE = 1;
    public const TYPE_LARAVEL = 2;

    /**
     * DeployInterface constructor.
     *
     * @param string $local_token
     * @param string $pulling_branch
     * @param string $work_dir
     */
    public function __construct($local_token, $pulling_branch, $work_dir);

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
        $deploy_type = self::TYPE_LARAVEL
    );
}
