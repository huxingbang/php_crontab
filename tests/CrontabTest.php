<?php

/**
 * Created by PhpStorm.
 * User: Jenner
 * Date: 2015/10/12
 * Time: 15:34
 */
class CrontabTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $log_file;

    protected $err_file;

    public function setUp()
    {
        $this->log_file = "/tmp/crontab_test.log." . getmypid();
        $this->err_file = "/tmp/crontab_err.log." . getmypid();
        if (file_exists($this->log_file)) unlink($this->log_file);
        if (file_exists($this->err_file)) unlink($this->err_file);
    }

    public function testStart()
    {
        if (file_exists($this->log_file)) {
            unlink($this->log_file);
        }
        $logger = \Jenner\Crontab\Logger\MissionLoggerFactory::create($this->log_file);
        $mission = new \Jenner\Crontab\Mission("mission_test", "ls /", "* * * * *", $logger);
        $crontab = new \Jenner\Crontab\Crontab(null, array($mission));

        $crontab->start(time());
        $out = file_get_contents($this->log_file);
        $except = shell_exec("ls /");
        $this->assertEquals($out, $except);
    }

    public function testError()
    {
        if (file_exists($this->log_file)) {
            unlink($this->log_file);
        }
        if (file_exists($this->err_file)) {
            unlink($this->err_file);
        }

        $out = \Jenner\Crontab\Logger\MissionLoggerFactory::create($this->log_file);
        $err = \Jenner\Crontab\Logger\MissionLoggerFactory::create($this->err_file);
        $mission = new \Jenner\Crontab\Mission("mission_test", "ls / && command_not_exists", "* * * * *", $out, $err);
        $crontab = new \Jenner\Crontab\Crontab(null, array($mission));

        $crontab->start(time());
        $stdout = file_get_contents($this->log_file);
        $except = shell_exec("ls /");
        $this->assertEquals($stdout, $except);
        $stderr = file_get_contents($this->err_file);
        $except = shell_exec('command_not_exists 2>&1');
        $this->assertEquals($stderr, $except);
    }

    public function testNotStart()
    {
        if (file_exists($this->log_file)) {
            unlink($this->log_file);
        }

        $logger = \Jenner\Crontab\Logger\MissionLoggerFactory::create($this->log_file);
        $mission = new \Jenner\Crontab\Mission("mission_test", "ls /", "3 * * * *", $logger);
        $crontab = new \Jenner\Crontab\Crontab(null, array($mission));

        $crontab->start(time());
        $this->assertFalse(file_exists($this->log_file));
    }

}