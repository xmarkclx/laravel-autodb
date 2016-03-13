<?php
namespace Inspyrd\AutoDB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class LaravelModelAssembler
 * Creates database schemas from Laravel models.
 *
 * @package Inspyrd
 *
 */
class LaravelModelAssembler extends ModelAssembler{
    private $table;
    private $assemblyInstructionSet;
    private $tableName;

    public function assemble(AssemblyInstructionSet $assemblyInstructions)
    {
        $this->assemblyInstructionSet = $assemblyInstructions;
        $this->tableName = $assemblyInstructions->tableName;

        if( !\Schema::hasTable($assemblyInstructions->tableName) ){
            \Schema::create($assemblyInstructions->tableName, function(Blueprint $table){
                $this->table = $table;
                $this->processInstructions();
            });
        }else{
            \Schema::table($assemblyInstructions->tableName, function(Blueprint $table){
                $this->table = $table;
                $this->processInstructions();
            });
        }
    }

    private function processInstructions()
    {
        /** @var Blueprint $table */
        $table = $this->table;

        // Add timestamps if not yet added
        if( !\Schema::hasColumn($this->tableName, 'created_at') ) {
            $table->timestamps();
        }

        foreach($this->assemblyInstructionSet->assemblyInstructions as $assemblyInstruction){
            /** @var AssemblyInstruction $assemblyInstruction */
            switch($assemblyInstruction->tag){
                case '@property':
                    if( !\Schema::hasColumn($this->tableName, $assemblyInstruction->name) ) {
                        if (!$assemblyInstruction->hasHashtags()) {
                            $table->{$assemblyInstruction->type}($assemblyInstruction->name);
                        } else {
                            $table->{$assemblyInstruction->getTypeTag()}($assemblyInstruction->name);
                        };
                    }else{
                        if (!$assemblyInstruction->hasHashtags()) {
                            $table->{$assemblyInstruction->type}($assemblyInstruction->name)->change();
                        } else {
                            $table->{$assemblyInstruction->getTypeTag()}($assemblyInstruction->name)->change();
                        };
                    }
                    break;
            }
        }
    }
}
