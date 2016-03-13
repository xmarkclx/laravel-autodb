<?php
namespace Inspyrd\AutoDB;

class Parser
{
    private static $instance;

    private function __construct()
    {
    }

    public function makeAssemblyInstructionsFromString($className, $string)
    {
        $assemblyInstructions = array();

        $properties = explode("\n", $string);
        foreach($properties as $propertyString){
            $assemblyInstruction = new AssemblyInstruction;

            // Get the first instance of @
            $start = strpos($propertyString, '@');
            $definitionString = substr($propertyString, $start);

            // Make array based on newline
            $definitionStringArray = explode(' ', $definitionString);

            // If less than 3 items, then it's not a viable definition
            // We need tag, type and name.
            if(count($definitionStringArray) < 3){
                continue;
            }

            // If we have extra docblocks, then it's probable that we have hashtags
            if(count($definitionStringArray >= 4)){
                $hashtags = $this->getHashtags($definitionString);
            }

            $assemblyInstruction->tag = $definitionStringArray[0];
            $assemblyInstruction->type = $definitionStringArray[1];
            $assemblyInstruction->name = str_replace('$', '', $definitionStringArray[2]);
            $assemblyInstruction->hashtags = $hashtags;
            array_push($assemblyInstructions, $assemblyInstruction);
        }

        $assemblyInstructionSet = new AssemblyInstructionSet();
        $assemblyInstructionSet->assemblyInstructions = $assemblyInstructions;
        $assemblyInstructionSet->tableName = $this->generateTableNameFrom($className);
        return $assemblyInstructionSet;
    }

    /**
     * @return Parser
     */
    public static function getInstance()
    {
        if(null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Gets the AutoDB section of the class from the doc block.
     * @param $class
     */
    public function getAutoDBSection($class)
    {
        $class = "\\".$class;

        $reflectionClass = new \ReflectionClass($class);
        $docComment = $reflectionClass->getDocComment();
        $autoDBSectionText = $this->getStringBetween($docComment, '@db===', '@db===');
        $autoDBSectionText = $this->trimLines($autoDBSectionText);
        return $autoDBSectionText;
    }

    private function getStringBetween($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    private function generateTableNameFrom($string)
    {
        return ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $string)), '_').'s';
    }

    private function trimLines($string)
    {
        return implode("\n", array_map('trim', explode("\n", $string)));
    }

    private function stripNewlines($string)
    {
        return str_replace(array("\r", "\n"), '', $string);
    }

    private function getHashtags($string) {
        $hashtags= FALSE;
        preg_match_all("/(#\w+)/u", $string, $matches);
        if ($matches) {
            $hashtagsArray = array_count_values($matches[0]);
            $hashtags = array_keys($hashtagsArray);
        }

        foreach($hashtags as $k => $v){
            $hashtags[$k] = str_replace('#', '', $v);
        }

        return $hashtags;
    }
}
