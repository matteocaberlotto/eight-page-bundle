<?php

namespace Eight\PageBundle\Seo;

/**
 *
 * @author teito
 */
class SeoPage
{
    protected $page;

    protected $title = '';
    protected $description = '';
    protected $metas = array();
    protected $encoding;

    public function __construct($title, $description) {
        $this->title = $title;
        $this->description = $description;
    }

    public function setPage($page)
    {
        $this->page = $page;
    }

    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    public function getEncoding()
    {
        return $this->encoding;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription($description)
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetas()
    {
        return $this->metas;
    }

    /**
     * {@inheritdoc}
     */
    public function addMeta($type, $name, $content, array $extras = array())
    {
        if (!isset($this->metas[$type])) {
            $this->metas[$type] = array();
        }

        $this->metas[$type][$name] = array($content, $extras);

        return $this;
    }
}
