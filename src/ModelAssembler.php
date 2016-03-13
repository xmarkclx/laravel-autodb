<?php
namespace Inspyrd\AutoDB;

/**
 * Class ModelAssembler
 * Creates database schemas from models.
 * Extend this to create your own Model Assembler.
 *
 * @package Inspyrd
 */
abstract class ModelAssembler{
    /**
     * Assemble the database given the assembly instructions.
     * @param array $assemblyInstructions
     */
    abstract public function assemble(AssemblyInstructionSet $assemblyInstructions);
}
