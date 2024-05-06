<?php

namespace ZankoKhaledi\LaravelSlug\Traits;


trait Slugable
{
    /**
     * @param string|null $slugLabel
     * @param string|null $spaceRepl
     * @param int|null $uniqueNumber
     * @return array|string
     */
    private function toSlug(?string $slugLabel = null, ?string $spaceRepl = "-", ?int $uniqueNumber = null): array|string
    {
        $string = $slugLabel;

        if (!is_null($string)) {
            $string = str_replace("&", "and", $string);
            $string = str_replace("(", "", $string);
            $string = str_replace(")", "", $string);
            $string = str_replace("", "", $string);
            $string = str_replace("|", "", $string);
            $string = str_replace("»", "", $string);
            $string = str_replace("«", "", $string);
            $string = str_replace("‌", "-", $string);
            //        $string = preg_replace("/[^a-zA-Z0-9 _-]/", "", $string);
            $string = strtolower($string);
            $string = preg_replace("/[ ]+/ui", " ", $string);
            $string = str_replace(" ", $spaceRepl, $string);

            if (!is_null($uniqueNumber)) {
                return $string . '-' . $uniqueNumber;
            }
            return $string;
        }

        return "";
    }


    /**
     * @param int $id
     * @param string $field
     * @param string $value
     * @return bool
     */
    private function isUnique(string $field ,string $value):bool
    {
        return static::query()->where('id','!=',$this->id)
            ->where($field,$value)->exists();
    }


    /**
     * @return void
     * @throws \ErrorException
     */
    private function saveSlug()
    {
         if(!is_array($this->slugable())){
             throw new \ErrorException("static::slugable() must returns an array with key value pair.");
         }
         
         $slugField = $this->slugable()['slug']['field'];
         $slugResource = $this->slugable()['slug']['resource'];
         
         if($this->slugable()['unique']){
             $this->forceFill([
                 $slugField => $this->isUnique($slugField,$this->{$slugField}) ? 
                     $this->toSlug($this->{$slugResource},'-',$this->id) : $this->toSlug($this->{$slugResource})
             ])->save();
         }else{
             $this->forceFill([
                 $slugField => $this->toSlug($this->{$slugResource})
             ])->save();
         }
    }


    /**
     * @return string[]
     */
    public function slugable(): array
    {
        return  [
            'slug' => [
                'field'    => 'slug',
                'resource' => 'title'
            ],
            'unique' => true,
        ];
    }
}