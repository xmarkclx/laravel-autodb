<?php
namespace Inspyrd\AutoDB;

class AutoDB
{
    /**
     * @param mixed $class Laravel Model name usually taken by using the ::class static property
     */
    public static function laravel_model_to_database_entries($class)
    {
        $parser = Parser::getInstance();
        $autoDBSection = $parser->getAutoDBSection($class);
        $parserResults = $parser->makeAssemblyInstructionsFromString(substr($class, strrpos($class, '\\') + 1), $autoDBSection);
        $modelAssembler = new LaravelModelAssembler();
        $modelAssembler->assemble($parserResults);
    }
}
