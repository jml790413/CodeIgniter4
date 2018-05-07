<?php namespace CodeIgniter\Commands;

use Config\MockAppConfig;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\CommandRunner;

class SessionsCommandsTest extends \CIUnitTestCase
{
	private $stream_filter;

	public function setUp()
	{
		CommandsTestStreamFilter::$buffer = '';
		$this->stream_filter = stream_filter_append(STDOUT, 'CommandsTestStreamFilter');

		$this->env = new \CodeIgniter\Config\DotEnv(ROOTPATH);
		$this->env->load();

		// Set environment values that would otherwise stop the framework from functioning during tests.
		if ( ! isset($_SERVER['app.baseURL']))
		{
			$_SERVER['app.baseURL'] = 'http://example.com';
		}

		$_SERVER['argv'] = ['spark', 'list'];
		$_SERVER['argc'] = 2;
		CLI::init();

		$this->config = new MockAppConfig();
		$this->request = new \CodeIgniter\HTTP\IncomingRequest($this->config, new \CodeIgniter\HTTP\URI('https://somwhere.com'), null, new UserAgent());
		$this->response = new \CodeIgniter\HTTP\Response($this->config);
		$this->runner = new CommandRunner($this->request, $this->response);
	}

	public function tearDown()
	{
		stream_filter_remove($this->stream_filter);
	}

	public function testCreateMigrationCommand()
	{
		$this->runner->index(['session:migration']);
		$result = CommandsTestStreamFilter::$buffer;

		// make sure we end up with a migration class in the right place
		// or at least that we claim to have done so
		// separate assertions avoid console color codes
		$this->assertContains('Created file:', $result);
		$this->assertContains('APPPATH/Database/Migrations/', $result);
		$this->assertContains('_create_ci_sessions_table.php', $result);
	}

	public function testOverriddenCreateMigrationCommand()
	{
		$_SERVER['argv'] = ['spark','session:migration', '-t', 'mygoodies'];
		$_SERVER['argc'] = 4;
		CLI::init();
		
		$this->runner->index(['session:migration']);
		$result = CommandsTestStreamFilter::$buffer;

		// make sure we end up with a migration class in the right place
		$this->assertContains('Created file:', $result);
		$this->assertContains('APPPATH/Database/Migrations/', $result);
		$this->assertContains('_create_mygoodies_table.php', $result);
	}


}
