<?php

namespace Eight\PageBundle\Entity;

use Eight\PageBundle\Model\PageInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="page")
 * @ORM\Entity(repositoryClass="Eight\PageBundle\Entity\PageRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Page implements PageInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $seq;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(
     *   targetEntity="Tag",
     *   inversedBy="pages",
     *   cascade={"persist", "remove"}
     * )
     * @ORM\JoinTable(name="page_tag_tag")
     */
    protected $tags;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    protected $metas_name;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    protected $metas_property;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    protected $metas_http_equiv;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var \Raindrop\RoutingBundle\Entity\Route
     *
     * @ORM\OneToOne(
     *   targetEntity="\Raindrop\RoutingBundle\Entity\Route",
     *   cascade={"persist", "remove"}
     * )
     */
    protected $route;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $layout;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *   targetEntity="Block",
     *   mappedBy="page",
     *   cascade={"persist", "remove"}
     * )
     */
    protected $blocks;

    /**
     * @ORM\Column(type="boolean",nullable=true)
     */
    protected $published;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    protected $published_from;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     */
    protected $published_to;

    protected $edit_mode = false;

    public function __construct() {
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->blocks = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->getTitle();
    }

    public function setEditMode()
    {
        $this->edit_mode = true;
    }

    public function editMode()
    {
        return $this->edit_mode == true;
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

    public function resetId()
    {
        $this->id = null;
    }

    /**
     * Set seq
     *
     * @param integer $seq
     * @return Page
     */
    public function setSeq($seq)
    {
        $this->seq = $seq;

        return $this;
    }

    /**
     * Get seq
     *
     * @return integer
     */
    public function getSeq()
    {
        return $this->seq;
    }

    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Page
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set body
     *
     * @param string $body
     * @return Page
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Page
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Add tags
     *
     * @param \Eight\PageBundle\Entity\Tag $tags
     * @return Bookmark
     */
    public function addTag(\Eight\PageBundle\Entity\Tag $tags)
    {
        $this->tags[] = $tags;

        return $this;
    }

    /**
     * Remove tags
     *
     * @param \Eight\PageBundle\Entity\Tag $tags
     */
    public function removeTag(\Eight\PageBundle\Entity\Tag $tags)
    {
        $this->tags->removeElement($tags);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTags()
    {
        return $this->tags;
    }

    public function hasTag($name)
    {
        foreach ($this->tags as $tag) {
            if ($name === $tag->getName()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Set tags
     *
     * @return Category
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Reservation
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
     * @return Reservation
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
     * Set route
     *
     * @param \Raindrop\RoutingBundle\Entity\Route $route
     * @return Page
     */
    public function setRoute(\Raindrop\RoutingBundle\Entity\Route $route = null)
    {
        $this->route = $route;

        $this->route->setContent($this);

        return $this;
    }

    /**
     * Get route
     *
     * @return \Raindrop\RoutingBundle\Entity\Route
     */
    public function getRoute()
    {
        return $this->route;
    }


    /**
     * Set metas_name
     *
     * @param array $metasName
     * @return Page
     */
    public function setMetasName($metasName)
    {
        $this->metas_name = $metasName;

        return $this;
    }

    /**
     * Get metas_name
     *
     * @return array
     */
    public function getMetasName()
    {
        return $this->metas_name;
    }

    /**
     * Set metas_property
     *
     * @param array $metasProperty
     * @return Page
     */
    public function setMetasProperty($metasProperty)
    {
        $this->metas_property = $metasProperty;

        return $this;
    }

    /**
     * Get metas_property
     *
     * @return array
     */
    public function getMetasProperty()
    {
        return $this->metas_property;
    }

    /**
     * Set metas_http_equiv
     *
     * @param array $metasHttpEquiv
     * @return Page
     */
    public function setMetasHttpEquiv($metasHttpEquiv)
    {
        $this->metas_http_equiv = $metasHttpEquiv;

        return $this;
    }

    public function getLocale()
    {
        if ($this->route) {
            return $this->route->getLocale();
        }
    }

    /**
     * Get metas_http_equiv
     *
     * @return array
     */
    public function getMetasHttpEquiv()
    {
        return $this->metas_http_equiv;
    }

    public function getTagsAsArray()
    {
        $return = array();

        foreach ($this->tags as $tag) {
            $return []= $tag->getName();
        }

        return $return;
    }

    public function getTagsAsCsv()
    {
        return implode(', ', $this->getTagsAsArray());
    }

    /**
     * Set published
     *
     * @param boolean $published
     * @return Page
     */
    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }

    /**
     * Get published
     *
     * @return boolean
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * Set published_from
     *
     * @param \DateTime $publishedFrom
     * @return Page
     */
    public function setPublishedFrom($publishedFrom)
    {
        $this->published_from = $publishedFrom;

        return $this;
    }

    /**
     * Get published_from
     *
     * @return \DateTime
     */
    public function getPublishedFrom()
    {
        return $this->published_from;
    }

    /**
     * Set published_to
     *
     * @param \DateTime $publishedTo
     * @return Page
     */
    public function setPublishedTo($publishedTo)
    {
        $this->published_to = $publishedTo;

        return $this;
    }

    /**
     * Get published_to
     *
     * @return \DateTime
     */
    public function getPublishedTo()
    {
        return $this->published_to;
    }

    /**
     * Set layout
     *
     * @param string $layout
     * @return Page
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * Get layout
     *
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Add blocks
     *
     * @param \Eight\PageBundle\Entity\Block $blocks
     * @return Page
     */
    public function addBlock(\Eight\PageBundle\Entity\Block $blocks)
    {
        $this->blocks[] = $blocks;

        return $this;
    }

    /**
     * Remove blocks
     *
     * @param \Eight\PageBundle\Entity\Block $blocks
     */
    public function removeBlock(\Eight\PageBundle\Entity\Block $blocks)
    {
        $this->blocks->removeElement($blocks);
    }

    /**
     * Get blocks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBlocks()
    {
        return $this->blocks;
    }

    public function getOrderedBlocks($type = 'default')
    {
        $blocks = $this->getBlocks();

        // filter proper block type
        $blocks = $blocks->filter(function ($entry) use ($type) {
            return $entry->getType() == $type;
        });

        // filter enabled when NOT in editing mode
        if (!$this->editMode()) {
            $blocks = $blocks->filter(function ($entry) {
                return $entry->isEnabled();
            });
        }

        // sort blocks
        $iterator = $blocks->getIterator();
        $iterator->uasort(function ($first, $second) {
            return (int) $first->getSeq() > (int) $second->getSeq() ? 1 : -1;
        });

        return $iterator;
    }

    public function getRootBlocks()
    {
        $return = array();

        foreach ($this->getBlocks() as $block) {
            if (!$block->getBlock()) {
                $return []= $block;
            }
        }

        return $return;
    }

    public function getBlocksAsArray()
    {
        $return = [];

        foreach ($this->getOrderedBlocks() as $block) {
            $return []= $block->asArray(true);
        }

        return $return;
    }

    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'url' => $this->getRoute()->getPath(),
            'locale' => $this->getLocale(),
            'controller' => $this->getRoute()->getController(),
            'title' => $this->getTitle(),
            'tags' => $this->getTagsAsArray(),
            'blocks' => $this->getBlocksAsArray(),
            );
    }
}
