<?php

namespace SrcLab\AutoDeploy\Test;

use ReflectionClass;
use SrcLab\AutoDeploy\AutoDeploy;
use PHPUnit\Framework\TestCase;

class AutoDeployTest extends TestCase
{
    public function testHashesNotEqual()
    {
        $deploy = new AutoDeploy([
            'token' => 'bad_local_token',
            'branch' => 'branch',
            'work_dir' => 'work_dir',
        ]);

        $this->expectExceptionMessage('Хэши не совпадают');
        $deploy->deploy('payload', 'hash', 'pull_request');
    }

    public function testNotActualEvent()
    {
        $result = $this->deployWithParams('github_payload', 'branch', 'not_actual_event');
        $this->assertFalse($result);
    }

    public function testPullRequestNotClosed()
    {
        $github_payload = file_get_contents('tests/fixtures/pull_request_not_closed.json');
        $result = $this->deployWithParams($github_payload, 'branch', 'pull_request');
        $this->assertFalse($result);
    }

    public function testPullRequestNotMerged()
    {
        $github_payload = file_get_contents('tests/fixtures/pull_request_not_merged.json');
        $result = $this->deployWithParams($github_payload, 'branch', 'pull_request');

        $this->assertFalse($result);
    }

    public function testPullRequestNotActualBranch()
    {
        $github_payload = file_get_contents('tests/fixtures/pull_request_not_actual_branch.json');
        $result = $this->deployWithParams($github_payload, 'branch', 'pull_request');

        $this->assertFalse($result);
    }

    public function testPullRequestDeployNotAllowed()
    {
        $github_payload = file_get_contents('tests/fixtures/pull_request_deploy_not_allowed.json');
        $result = $this->deployWithParams($github_payload, 'branch', 'pull_request');

        $this->assertFalse($result);
    }

    public function testPushNotActualBranch()
    {
        $github_payload = file_get_contents('tests/fixtures/push_not_actual_branch.json');
        $result = $this->deployWithParams($github_payload, 'branch', 'push');

        $this->assertFalse($result);
    }

    public function testPullRequestDeploy()
    {
        $github_payload = file_get_contents('tests/fixtures/pull_request.json');
        $this->expectExceptionMessage('The provided cwd "work_dir" does not exist.');
        $this->deployWithParams($github_payload, 'branch', 'pull_request');
    }

    public function testPushDeploy()
    {
        $github_payload = file_get_contents('tests/fixtures/push.json');
        $this->expectExceptionMessage('The provided cwd "work_dir" does not exist.');
        $this->deployWithParams($github_payload, 'branch', 'push');
    }

    protected function deployWithParams($github_payload, $branch, $github_event, $deploy_type = AutoDeploy::LARAVEL_TYPE)
    {
        $deploy = new AutoDeploy([
            'token' => 'token',
            'branch' => $branch,
            'work_dir' => 'work_dir',
        ]);

        $github_hash = $this->getPrivateMethodResult($deploy, 'getLocalHash', [$github_payload]);

        return $deploy->deploy($github_payload, $github_hash, $github_event, $deploy_type);
    }

    protected function getPrivateMethodResult($object, $method, $args)
    {
        $class = new ReflectionClass($object);
        $method = $class->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $args);
    }
}
