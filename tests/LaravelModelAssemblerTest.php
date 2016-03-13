<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Illuminate\Support\Facades\Schema;
use Inspyrd\AutoDB\Parser;
use Inspyrd\Test\TestModel;

require_once('models/TestModel.php');

/**
 * Class LaravelModelAssemblerTest
 * @property \Inspyrd\AutoDB\LaravelModelAssembler $assembler;
 */
class LaravelModelAssemblerTest extends TestCase
{
    public function testCreateTableIfNotExist()
    {
        // ModelGenerator must create table
        $this->assembler->assemble($this->assemblyInstructions);

        // Check if table exists
        $this->assertTrue(Schema::hasTable('test_models'));
    }

    public function testDontDestroyTableContentsIfExist()
    {
        // Given table does exist and has row
        $this->assembler->assemble($this->assemblyInstructions);
        $this->assertTrue(Schema::hasTable('test_models'));

        $test_model = new TestModel;
        $test_model->id = 1;
        $test_model->avatar_url = 'test';
        $test_model->save();

        // When we run database generator
        $this->assembler->assemble($this->assemblyInstructions);

        // Ensure that content still exists
        $testModel = TestModel::find(1);
        $this->assertEquals('test', $testModel->avatar_url);

        // Check if table exists
        $this->assertTrue(Schema::hasTable('test_models'));
    }

    public function testCreatePropertyIfNotExist()
    {
        $this->assembler->assemble($this->assemblyInstructions);
        $this->assertTrue( \Schema::hasColumn('test_models', 'avatar_url') );
    }

    public function testModifyPropertyIfModified()
    {
        \Schema::dropIfExists('test_models');
        $this->assembler->assemble($this->assemblyInstructions);
        $type = DB::connection()->getDoctrineColumn('test_models', 'avatar_url')->getType()->getName();
        $this->assertEquals('string', $type);

        $this->assembler->assemble($this->assemblyInstructions2);
        $type = DB::connection()->getDoctrineColumn('test_models', 'avatar_url')->getType()->getName();
        $this->assertEquals('integer', $type);
    }

    public function setUp()
    {
        parent::setUp();

        $class = \Inspyrd\Test\TestModel::class;
        $parser = Parser::getInstance();
        $autoDBSection = $parser->getAutoDBSection($class);

        $this->assembler = new \Inspyrd\AutoDB\LaravelModelAssembler();
        $this->assemblyInstructions = $parser->makeAssemblyInstructionsFromString(substr($class, strrpos($class, '\\') + 1), $autoDBSection);

        $this->assemblyInstructions2 = clone($this->assemblyInstructions);
        $this->assemblyInstructions2->assemblyInstructions[0] = clone($this->assemblyInstructions->assemblyInstructions[0]);
        $this->assemblyInstructions2->assemblyInstructions[0]->type = 'integer';

        \Schema::dropIfExists('test_models');
    }
}
