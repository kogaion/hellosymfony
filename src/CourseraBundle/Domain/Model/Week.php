<?php
/**
 * Created by PhpStorm.
 * User: Kogaion
 * Date: 10/28/2017
 * Time: 7:41 PM
 */

namespace CourseraBundle\Domain\Model;


class Week
{
    protected $id;
    protected $name;
    protected $slug;
//    protected $description;
//    protected $timeCommitment;
//    protected $learningObjectives;
    /**
     * @var Module[]
     */
    protected $elements;

    /**
     * @param $jsonArray
     * @return Week
     */
    public static function build($jsonArray)
    {
        $w = new Week;
        $w->id = $jsonArray['id'];
        $w->name = $jsonArray['name'];
        $w->slug = $jsonArray['slug'];
        foreach ($jsonArray['elements'] as $el) {
            $w->elements[] = Module::build($el);
        }
        return $w;
    }

    /**
     * @return Module[]
     */
    public function getModules()
    {
        return $this->elements;
    }
}