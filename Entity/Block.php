<?php

namespace Eight\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="block")
 * @ORM\Entity(repositoryClass="Eight\PageBundle\Entity\BlockRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Block implements BlockInterface
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
    protected $name;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $type;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $seq;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $layout;

    /**
     * @ORM\Column(type="boolean",nullable=true)
     */
    protected $enabled;

    /**
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="blocks")
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id")
     */
    protected $page;

    /**
     * @ORM\ManyToOne(targetEntity="Block", inversedBy="blocks")
     * @ORM\JoinColumn(name="block_id", referencedColumnName="id")
     */
    protected $block;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *   targetEntity="Block",
     *   mappedBy="block",
     *   cascade={"persist", "remove"}
     * )
     */
    protected $blocks;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *   targetEntity="Content",
     *   mappedBy="block",
     *   cascade={"persist", "remove"}
     * )
     */
    protected $contents;

    protected $loads_variables = false;

    public function __toString()
    {
        return (string) $this->getName();
    }

    public function setLoadsVariables()
    {
        $this->loads_variables = true;
    }

    public function loadsVariables()
    {
        return $this->loads_variables;
    }

    /**
     * Add contents
     *
     * @param \Eight\PageBundle\Entity\Content $contents
     * @return Page
     */
    public function addContent(\Eight\PageBundle\Entity\Content $contents)
    {
        $this->contents[] = $contents;

        return $this;
    }

    /**
     * Remove contents
     *
     * @param \Eight\PageBundle\Entity\Content $contents
     */
    public function removeContent(\Eight\PageBundle\Entity\Content $contents)
    {
        $this->contents->removeElement($contents);
    }

    /**
     * Get contents
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContents()
    {
        return $this->contents;
    }

    public function getContent($name)
    {
        $contents = $this->contents;
        foreach ($contents as $content)
        {
            if ($content->getName() == $name) {
                return $content;
            }
        }

        return false;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->contents = new \Doctrine\Common\Collections\ArrayCollection();
        $this->blocks = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Block
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
     * Set seq
     *
     * @param integer $seq
     * @return Block
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

    /**
     * Set layout
     *
     * @param string $layout
     * @return Block
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
     * Set page
     *
     * @param \Eight\PageBundle\Entity\Page $page
     * @return Block
     */
    public function setPage(\Eight\PageBundle\Entity\Page $page = null)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get page
     *
     * @return \Eight\PageBundle\Entity\Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set block
     *
     * @param \Eight\PageBundle\Entity\Block $block
     * @return Block
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
     * Add blocks
     *
     * @param \Eight\PageBundle\Entity\Block $blocks
     * @return Block
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

    /**
     * Set type
     *
     * @param string $type
     * @return Block
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

    public function hasChildren()
    {
        return count($this->blocks) > 0;
    }

    public function getOrderedBlocks($page)
    {
        $blocks = $this->getBlocks();

        if (!$page->editMode()) {
            $blocks = $blocks->filter(function ($entry) {
                return $entry->isEnabled();
            });
        }

        $iterator = $blocks->getIterator();

        $iterator->uasort(function ($first, $second) {
            return (int) $first->getSeq() > (int) $second->getSeq() ? 1 : -1;
        });

        return $iterator;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return Block
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Is enabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->getEnabled();
    }
}
