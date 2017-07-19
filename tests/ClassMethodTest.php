<?php

use DALTCORE\LaravelDeployHelper\Console\Commands\Deploy;
use DALTCORE\LaravelDeployHelper\Console\Commands\Locktest;
use DALTCORE\LaravelDeployHelper\Console\Commands\Patch;
use DALTCORE\LaravelDeployHelper\Console\Commands\Rollback;
use DALTCORE\LaravelDeployHelper\Helpers\Command;
use DALTCORE\LaravelDeployHelper\Helpers\Deployer;
use DALTCORE\LaravelDeployHelper\Helpers\Git;
use DALTCORE\LaravelDeployHelper\Helpers\Locker;
use DALTCORE\LaravelDeployHelper\Helpers\SSH;
use DALTCORE\LaravelDeployHelper\Remote\RemoteManager;
use SebastiaanLuca\Helpers\Classes\MethodHelper;

class ClassMethodTest extends TestCase
{
    /**
     * @test
     */
    public function testIfDeployHasConstructor()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(Deploy::class, '__construct', 'public'));
    }

    /**
     * @test
     */
    public function testIfDeployHasFreshInit()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(Deploy::class, 'handle', 'public'));
    }

    /**
     * @test
     */
    public function testIfLocktestHasConstructor()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(Locktest::class, '__construct', 'public'));
    }

    /**
     * @test
     */
    public function testIfLocktestHasFreshInit()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(Locktest::class, 'handle', 'public'));
    }

    /**
     * @test
     */
    public function testIfPatchHasConstructor()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(Patch::class, '__construct', 'public'));
    }

    /**
     * @test
     */
    public function testIfPatchHasFreshInit()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(Patch::class, 'handle', 'public'));
    }

    /**
     * @test
     */
    public function testIfRollbackHasConstructor()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(Rollback::class, '__construct', 'public'));
    }

    /**
     * @test
     */
    public function testIfRollbackHasFreshInit()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(Rollback::class, 'handle', 'public'));
    }

    /**
     * @test
     */
    public function testIfCommandHasCommand()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(Command::class, 'command', 'protected'));
    }

    /**
     * @test
     */
    public function testIfCommandHasBuilder()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(Command::class, 'builder', 'public'));
    }

    /**
     * @test
     */
    public function testIfDeployerHasFreshInit()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(Deployer::class, 'freshInit', 'public'));
    }

    /**
     * @test
     */
    public function testIfDeployerHasDoDeploy()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(Deployer::class, 'doDeploy', 'public'));
    }

    /**
     * @test
     */
    public function testIfDeployerHasDoRollback()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(Deployer::class, 'doRollback', 'public'));
    }

    /**
     * @test
     */
    public function testIfDeployerHasDoPatch()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(Deployer::class, 'doPatch', 'public'));
    }

    /**
     * @test
     */
    public function testIfGitHasGetBranches()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(Git::class, 'getBranches', 'public'));
    }

    /**
     * @test
     */
    public function testIfGitHasGetBLastCommit()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(Git::class, 'getLastCommit', 'public'));
    }

    /**
     * @test
     */
    public function testIfLockerHasVerify()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(Locker::class, 'verify', 'public'));
    }

    /**
     * @test
     */
    public function testIfLockerHasGetLockPath()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(Locker::class, 'getLockPath', 'public'));
    }

    /**
     * @test
     */
    public function testIfLockerHasLock()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(Locker::class, 'lock', 'public'));
    }

    /**
     * @test
     */
    public function testIfLockerHasUnlock()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(Locker::class, 'unlock', 'public'));
    }

    /**
     * @test
     */
    public function testIfSshHasInstance()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(SSH::class, 'instance', 'public'));
    }

    /**
     * @test
     */
    public function testIfSshHasHome()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(SSH::class, 'home', 'public'));
    }

    /**
     * @test
     */
    public function testIfSshHasCheckAppVersion()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(SSH::class, 'checkAppVersion', 'public'));
    }

    /**
     * @test
     */
    public function testIfSshHasExecute()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(SSH::class, 'execute', 'public'));
    }

    /**
     * @test
     */
    public function testIfSshHasPreFlight()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(SSH::class, 'preFlight', 'public'));
    }

    /**
     * @test
     */
    public function testIfSshHasPerformLanding()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(SSH::class, 'performLanding', 'public'));
    }

    /**
     * @test
     */
    public function testIfRemoteHasPeConstructor()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(RemoteManager::class, '__construct', 'public'));
    }

    /**
     * @test
     */
    public function testIfRemoteHasInto()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(RemoteManager::class, 'into', 'public'));
    }

    /**
     * @test
     */
    public function testIfRemoteHasConnection()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(RemoteManager::class, 'connection', 'public'));
    }

    /**
     * @test
     */
    public function testIfRemoteHasConnect()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(RemoteManager::class, 'connect', 'public'));
    }

    /**
     * @test
     */
    public function testIfRemoteHasMultiple()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(RemoteManager::class, 'multiple', 'public'));
    }

    /**
     * @test
     */
    public function testIfRemoteHasResolve()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(RemoteManager::class, 'resolve', 'public'));
    }

    /**
     * @test
     */
    public function testIfRemoteHasMakeConnection()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(RemoteManager::class, 'makeConnection', 'protected'));
    }

    /**
     * @test
     */
    public function testIfRemoteHasSetOutput()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(RemoteManager::class, 'setOutput', 'protected'));
    }

    /**
     * @test
     */
    public function testIfRemoteHasGetAuth()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(RemoteManager::class, 'getAuth', 'protected'));
    }

    /**
     * @test
     */
    public function testIfRemoteHasGetConfig()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(RemoteManager::class, 'getConfig', 'protected'));
    }

    /**
     * @test
     */
    public function testIfRemoteHasGetDefaultConnection()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(RemoteManager::class, 'getDefaultConnection', 'public'));
    }

    /**
     * @test
     */
    public function testIfRemoteHasGroup()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(RemoteManager::class, 'group', 'public'));
    }

    /**
     * @test
     */
    public function testIfRemoteHasSetDefaultConnection()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(RemoteManager::class, 'setDefaultConnection', 'public'));
    }

    /**
     * @test
     */
    public function testIfRemoteHasCall()
    {
        $this->assertTrue(MethodHelper::hasMethodOfType(RemoteManager::class, '__call', 'public'));
    }
}
