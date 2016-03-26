<?php

// Testing for the Environment.php file.
// Created by: Matthew Bowker [[User:Matthewrbowker]]

class EnvironmentTest extends PHPUnit_Framework_TestCase
{
	private $toolVersion;
	private $environment;

	public function setUp() {
		$this->environment = new \Waca\Environment();
		$this->toolVersion = exec("git describe --always --dirty");
	}

	public function tearDown() {
		$this->environment = null;
		$this->toolVersion = null;
	}

	public function testGetToolVersion() {
		$this->assertEquals($this->environment->getToolVersion(), $this->toolVersion);
		$this->assertNotEquals($this->environment->getToolVersion(), null);
		$this->assertNotEquals($this->environment->getToolVersion(), "");
	}
}
