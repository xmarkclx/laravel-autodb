<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Inspyrd\Test\TestModel;
use Inspyrd\AutoDB\AutoDB;

require_once('models/TestModel.php');

class AutoDBTest extends TestCase
{
    public function testCreateTableIfNotExist()
    {
        // Given table does not exist
        Schema::dropIfExists('test_models');

        // ModelGenerator must create table
        AutoDB::laravel_model_to_database_entries(TestModel::class);

        // Check if table exists
        $this->assertTrue(Schema::hasTable('test_models'));
    }

    public function testDontDestroyTableIfExist()
    {
        // Given table does exist and has row
        $this->assertTrue(Schema::hasTable('test_models'));

        $test_model = new TestModel;
        $test_model->id = 1;
        $test_model->avatar_url = 'test';
        $test_model->save();

        // When we run database generator
        AutoDB::laravel_model_to_database_entries(TestModel::class);

        // Ensure that content still exists
        $testModel = TestModel::find(1);
        $this->assertEquals('test', $testModel->avatar_url);

        // Check if table exists
        $this->assertTrue(Schema::hasTable('test_models'));
    }

    public function setUp()
    {
        parent::setUp();
        $this->dataDefinition =
            PHP_EOL.
            '*  @property string $avatar_url'.PHP_EOL.
            '*  @property int $id #increments'.PHP_EOL.
            '*';
    }
}

