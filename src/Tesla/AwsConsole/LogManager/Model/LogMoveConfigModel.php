<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 06/02/14
 * Time: 12:57
 */

namespace Tesla\AwsConsole\LogManager\Model;

use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;

class LogMoveConfigModel
{

    private $dir = null;
    private $name = null;
    private $deleteAfterTransfer = false;
    private $s3Dir = null;
    private $s3Bucket = null;
    private $s3Acl = 'private';

    /**
     * Set the deleteAfterTransfer
     * @param boolean $deleteAfterTransfer
     */
    public function setDeleteAfterTransfer($deleteAfterTransfer)
    {
        $this->deleteAfterTransfer = $deleteAfterTransfer;

        return $this;
    }

    /**
     * Get the DeleteAfterTransfer
     * @return boolean
     */
    public function getDeleteAfterTransfer()
    {
        return $this->deleteAfterTransfer;
    }


    /**
     * Set the name
     * @param null $name
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the Name
     * @return null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the dir
     * @param null $dir
     */
    public function setDir($dir)
    {
        $this->dir = $dir;

        return $this;
    }

    /**
     * Get the Dir
     * @return null
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * Set the s3Dir
     * @param null $s3Dir
     */
    public function setS3Dir($s3Dir)
    {
        $this->s3Dir = $s3Dir;

        return $this;
    }

    /**
     * Get the S3Dir
     * @return null
     */
    public function getS3Dir()
    {
        return $this->s3Dir;
    }

    /**
     * Set the s3Bucket
     * @param null $s3Bucket
     */
    public function setS3Bucket($s3Bucket)
    {
        $this->s3Bucket = $s3Bucket;

        return $this;
    }

    /**
     * Get the S3Bucket
     * @return null
     */
    public function getS3Bucket()
    {
        return $this->s3Bucket;
    }

    /**
     * Set the s3Acl
     * @param string $s3Acl
     */
    public function setS3Acl($s3Acl)
    {
        $this->s3Acl = $s3Acl;

        return $this;
    }

    /**
     * Get the S3Acl
     * @return string
     */
    public function getS3Acl()
    {
        return $this->s3Acl;
    }


    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata
            ->addPropertyConstraint('dir', new Assert\NotNull())
            ->addPropertyConstraint('dir', new Assert\Type('string'))
            ->addPropertyConstraint(
                'dir',
                new Assert\Regex(array(
                        'pattern' => '/log/',
                        'message' => 'the pattern MUST contain the word "log" in its path'
                    )
                )
            )
            ->addPropertyConstraint('name', new Assert\Type('string'))
            ->addPropertyConstraint('name', new Assert\NotNull())
            ->addPropertyConstraint('s3Dir', new Assert\Type('string'))
            ->addPropertyConstraint('s3Dir', new Assert\NotNull())
            ->addPropertyConstraint('s3Bucket', new Assert\Type('string'))
            ->addPropertyConstraint('s3Bucket', new Assert\NotNull())
            ->addPropertyConstraint('s3Acl', new Assert\NotNull())
            ->addPropertyConstraint('s3Acl', new Assert\Type('string'))
            ->addPropertyConstraint('deleteAfterTransfer', new Assert\Type('boolean'))
            ;
    }


} 