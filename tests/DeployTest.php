<?php

namespace SrcLab\AutoDeploy\Test;

use ReflectionClass;
use SrcLab\AutoDeploy\Deploy;
use PHPUnit\Framework\TestCase;
use SrcLab\AutoDeploy\DeployInterface;

class DeployTest extends TestCase
{
    public function testHashesNotEqual()
    {
        $deploy = new Deploy('bad_local_token', 'pulling_branch', 'work_dir');

        $this->expectExceptionMessage('Хэши не совпадают');
        $deploy->deploy('payload', 'hash', 'pull_request');
    }

    public function testNotActualEvent()
    {
        $result = $this->deployWithParams('github_payload', 'pulling_branch', 'not_actual_event', 'allowed_label');
        $this->assertFalse($result);
    }

    public function testPullRequestNotClosed()
    {
        $github_payload = file_get_contents('tests/fixtures/pull_request_not_closed.json');
        $result = $this->deployWithParams($github_payload, 'pulling_branch', 'pull_request', 'allowed_label');

        $this->assertFalse($result);
    }

    public function testPullRequestNotMerged()
    {
        $github_payload = file_get_contents('tests/fixtures/pull_request_not_merged.json');
        $result = $this->deployWithParams($github_payload, 'pulling_branch', 'pull_request', 'allowed_label');

        $this->assertFalse($result);
    }

    public function testPullRequestNotActualBranch()
    {
        $github_payload = file_get_contents('tests/fixtures/pull_request_not_actual_branch.json');
        $result = $this->deployWithParams($github_payload, 'pulling_branch', 'pull_request', 'allowed_label');

        $this->assertFalse($result);
    }

    public function testPullRequestDeployNotAllowed()
    {
        $github_payload = file_get_contents('tests/fixtures/pull_request_deploy_not_allowed.json');
        $result = $this->deployWithParams($github_payload, 'pulling_branch', 'pull_request', 'allowed_label');

        $this->assertFalse($result);
    }

    public function testPushNotActualBranch()
    {
        $github_payload = file_get_contents('tests/fixtures/push_not_actual_branch.json');
        $result = $this->deployWithParams($github_payload, 'pulling_branch', 'push');

        $this->assertFalse($result);
    }

    public function testPullRequestDeploy()
    {
        $github_payload = file_get_contents('tests/fixtures/pull_request.json');

        $this->expectExceptionMessage('The provided cwd "work_dir" does not exist.');
        $this->deployWithParams($github_payload, 'pulling_branch', 'pull_request', 'allowed_label');
    }

    public function testPushDeploy()
    {
        $github_payload = file_get_contents('tests/fixtures/push.json');

        $this->expectExceptionMessage('The provided cwd "work_dir" does not exist.');
        $this->deployWithParams($github_payload, 'pulling_branch', 'push');
    }

    protected function deployWithParams(
        $github_payload,
        $pulling_branch,
        $github_event,
        $allowed_label = '',
        $deploy_type = DeployInterface::TYPE_LARAVEL
    ) {
        $local_token = 'local_token';
        $deploy = new Deploy($local_token, $pulling_branch, 'work_dir');
        $github_hash = $this->getPrivateMethodResult($deploy, 'getLocalHash', [$github_payload, $local_token]);

        return $deploy->deploy($github_payload, $github_hash, $github_event, $allowed_label, $deploy_type);
    }

    protected function getPrivateMethodResult($object, $method, $args)
    {
        $class = new ReflectionClass($object);
        $method = $class->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $args);
    }
}
