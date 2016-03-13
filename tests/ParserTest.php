<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Inspyrd\Test\TestModel;
use Inspyrd\AutoDB\Parser;
use Illuminate\Support\Facades\Schema;
require_once('models/TestModel.php');

/**
 * Class ParserTest
 * @property Parser $parser
 */
class ParserTest extends TestCase
{
    private $parser;
    private $dataDefinition;

    public function testGetAutoDBSection()
    {
        $actual = $this->parser->getAutoDBSection(TestModel::class);
        $expected = $this->dataDefinition;
        $this->assertEquals($expected, $actual, 'Auto DB Section does not parse properly');
    }

    public function testMakeAssemblyInstructionsFromString()
    {
        $assembly = $this->parser->makeAssemblyInstructionsFromString('TestModel', $this->dataDefinition);
        /** @var \Inspyrd\AutoDB\AssemblyInstructionSet $assembly */

        $avatar = $assembly->assemblyInstructions[0];
        $id = $assembly->assemblyInstructions[1];

        /** @var \Inspyrd\AutoDB\AssemblyInstruction $avatar; */
        /** @var \Inspyrd\AutoDB\AssemblyInstruction $id; */

        $this->assertEquals('test_models', $assembly->tableName);

        $this->assertEquals('@property', $avatar->tag);
        $this->assertEquals('string', $avatar->type);
        $this->assertEquals('avatar_url', $avatar->name);
        $this->assertEquals(0, count($avatar->hashtags));

        $this->assertEquals('@property', $id->tag);
        $this->assertEquals('int', $id->type);
        $this->assertEquals('id', $id->name);
        $this->assertEquals('increments', $id->hashtags[0]);
    }

    public function setUp()
    {
        parent::setUp();
        $this->parser = Parser::getInstance();
        $this->dataDefinition =
            PHP_EOL.
            '*  @property string $avatar_url'.PHP_EOL.
            '*  @property int $id #increments'.PHP_EOL.
            '*';
    }
}
