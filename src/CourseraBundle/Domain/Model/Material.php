<?php
/**
 * Created by PhpStorm.
 * User: Kogaion
 * Date: 10/28/2017
 * Time: 7:45 PM
 */

namespace CourseraBundle\Domain\Model;


class Material
{
    protected $id;
    protected $name;
    protected $slug;
//    protected $timeCommitment;
    /**
     * @var Content
     */
    protected $contentSummary;
//    protected $isLocked;
//    protected $trackId;

    /**
     * @param $jsonArray
     * @return Material
     */
    public static function build($jsonArray)
    {
        $m = new Material;
        $m->id = $jsonArray['id'];
        $m->name = $jsonArray['name'];
        $m->slug = $jsonArray['slug'];
        $m->contentSummary = Content::build($jsonArray['contentSummary']);
        return $m;
    }

    /**
     * @return bool
     */
    public function isLecture()
    {
        return $this->contentSummary->isLecture();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }
}