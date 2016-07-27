<?php

namespace Eight\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="page_tag")
 * @ORM\Entity(repositoryClass="Eight\PageBundle\Entity\TagRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Tag
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(unique=true)
     */
    protected $name;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(
     *   targetEntity="Page",
     *   mappedBy="tags"
     * )
     */
    protected $pages;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->pages = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Tag
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get pages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Add pages
     *
     * @param \Eight\PageBundle\Entity\Page $pages
     * @return Tag
     */
    public function addPage(\Eight\PageBundle\Entity\Page $pages)
    {
        $this->pages[] = $pages;

        return $this;
    }

    /**
     * Remove pages
     *
     * @param \Eight\PageBundle\Entity\Page $pages
     */
    public function removePage(\Eight\PageBundle\Entity\Page $pages)
    {
        $this->pages->removeElement($pages);
    }
}
