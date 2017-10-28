<?php
/**
 * Created by PhpStorm.
 * User: Kogaion
 * Date: 10/28/2017
 * Time: 7:37 PM
 */

namespace CourseraBundle\Domain\Model;


class Module
{
    protected $id;
    protected $name;
    protected $slug;
//    protected $timeCommitment;
//    protected $trackId;
    /**
     * @var Material[]
     */
    protected $elements;

    /**
     * @param $jsonArray
     * @return Module
     */
    public static function build($jsonArray)
    {
        $m = new Module;
        $m->id = $jsonArray['id'];
        $m->name = $jsonArray['name'];
        $m->slug = $jsonArray['slug'];
        foreach ($jsonArray['elements'] as $el) {
            $m->elements[] = Material::build($el);
        }
        return $m;
    }

    /**
     * @return Material[]
     */
    public function getMaterials()
    {
        return $this->elements;
    }
}