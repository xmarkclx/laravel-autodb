<?php
namespace Inspyrd\AutoDB;

class AssemblyInstruction
{
    public $tag;
    public $type;
    public $name;
    public $hashtags;

    public function hasHashtags()
    {
        if(count($this->hashtags))
            return true;
        return false;
    }

    public function getTypeTag()
    {
        if($this->hasHashtags()){
            return $this->hashtags[0];
        }
    }
}
