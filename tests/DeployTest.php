<?php

namespace SrcLab\AutoDeploy\Test;

use ReflectionClass;
use SrcLab\AutoDeploy\Deploy;
use PHPUnit\Framework\TestCase;

class DeployTest extends TestCase
{
    public function testHashesNotEqual()
    {
        $deploy = new Deploy([
            'local_token' => 'bad_local_token',
            'pulling_branch' => 'pulling_branch',
            'work_dir' => 'work_dir',
        ]);

        $this->expectExceptionMessage('Хэши не совпадают');
        $deploy->deploy('payload', 'hash', 'pull_request');
    }

    public function testNotActualEvent()
    {
        $result = $this->deployWithParams('github_payload', 'pulling_branch', 'not_actual_event');
        $this->assertFalse($result);
    }

    public function testPullRequestNotClosed()
    {
        $github_payload = file_get_contents('tests/fixtures/pull_request_not_closed.json');
        $result = $this->deployWithParams($github_payload, 'pulling_branch', 'pull_request');
        $this->assertFalse($result);
    }

    public function testPullRequestNotMerged()
    {
        $github_payload = file_get_contents('tests/fixtures/pull_request_not_merged.json');
        $result = $this->deployWithParams($github_payload, 'pulling_branch', 'pull_request');

        $this->assertFalse($result);
    }

    public function testPullRequestNotActualBranch()
    {
        $github_payload = file_get_contents('tests/fixtures/pull_request_not_actual_branch.json');
        $result = $this->deployWithParams($github_payload, 'pulling_branch', 'pull_request');

        $this->assertFalse($result);
    }

    public function testPullRequestDeployNotAllowed()
    {
        $github_payload = file_get_contents('tests/fixtures/pull_request_deploy_not_allowed.json');
        $result = $this->deployWithParams($github_payload, 'pulling_branch', 'pull_request');

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
        $this->deployWithParams($github_payload, 'pulling_branch', 'pull_request');
    }

    public function testPushDeploy()
    {
        $github_payload = file_get_contents('tests/fixtures/push.json');
        $this->expectExceptionMessage('The provided cwd "work_dir" does not exist.');
        $this->deployWithParams($github_payload, 'pulling_branch', 'push');
    }

    protected function deployWithParams($github_payload, $pulling_branch, $github_event, $deploy_type = Deploy::LARAVEL_TYPE)
    {
        $deploy = new Deploy([
            'local_token' => 'local_token',
            'pulling_branch' => $pulling_branch,
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
