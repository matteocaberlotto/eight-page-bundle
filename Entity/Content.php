<?php

namespace Eight\PageBundle\Entity;

use Eight\PageBundle\Model\ContentInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="content")
 * @ORM\Entity(repositoryClass="Eight\PageBundle\Entity\ContentRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Content implements ContentInterface
{
    const CMS_IMAGES_FOLDER = '/uploads/images/cms';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $type;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    protected $content;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @ORM\ManyToOne(targetEntity="Block", inversedBy="contents")
     * @ORM\JoinColumn(name="block_id", referencedColumnName="id")
     */
    protected $block;

    /**
     * helper propery for upload, not mapped.
     */
    protected $image_path;

    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'name' => $this->getName(),
            'type' => $this->getType(),
            'content' => $this->getContent(),
            );
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Content
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Content
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Content
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return Content
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->created = new \DateTime();
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setUpdatedAtValue()
    {
        $this->updated = new \DateTime();
    }

    /**
     * Set block
     *
     * @param \Eight\PageBundle\Entity\Block $block
     * @return Content
     */
    public function setBlock(\Eight\PageBundle\Entity\Block $block = null)
    {
        $this->block = $block;

        return $this;
    }

    /**
     * Get block
     *
     * @return \Eight\PageBundle\Entity\Block
     */
    public function getBlock()
    {
        return $this->block;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Content
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    public function getImage()
    {
        if (!empty($this->content)) {
            return $this->content;
        }
    }

    /**
     * Set image_path
     *
     * @param string $image_path
     *
     * @return Content
     */
    public function setImagePath($image_path)
    {
        $this->image_path = $image_path;

        return $this;
    }

    /**
     * Get Image
     *
     * @return string
     */
    public function getImagePath()
    {
        return $this->image_path;
    }
}
