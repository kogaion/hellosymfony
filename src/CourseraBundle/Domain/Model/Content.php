<?php
/**
 * Created by PhpStorm.
 * User: Kogaion
 * Date: 10/28/2017
 * Time: 7:48 PM
 */

namespace CourseraBundle\Domain\Model;


class Content
{
    protected $typeName;
//    var $definition;

    /**
     * @param $jsonArray
     * @return Content|LectureContent
     */
    public static function build($jsonArray)
    {
        switch ($jsonArray['typeName']) {
            case 'lecture':
                $c = new LectureContent;
                return $c;
            default:
                $c = new Content;
                $c->typeName = $jsonArray['typeName'];
                return $c;
        }
    }

    public function isLecture()
    {
        return $this instanceof LectureContent;
    }
}