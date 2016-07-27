<?php

namespace Eight\PageBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Collections\ArrayCollection;

class TagsToStringTransformer implements DataTransformerInterface
{

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * fqn
     */
    protected $className;

    /**
     * @param ObjectManager $om
     */
    public function __construct($className, $om)
    {
        $this->om = $om;
        $this->className = $className;
    }

    /**
     * Transforms an array collection (tags) to a string (number).
     *
     * @param  Issue|null $issue
     * @return string
     */
    public function transform($tags)
    {
        if (null === $tags) {
            return "";
        }

        if (is_string($tags)) {
            return $tags;
        }

        return implode(', ', $tags->toArray());
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  string $number
     *
     * @return Issue|null
     *
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($tags)
    {
        if (!$tags) {
            return null;
        }

        $tagsCollection = new ArrayCollection();

        foreach (explode(',', $tags) as $tag) {

            $cleanTag = trim($tag);

            $tagObject = $this->om
                ->findOneBy($this->className, array('name' => $cleanTag))
            ;

            if (null === $tagObject) {
                $tagObject = new $this->className;
                $tagObject->setName($cleanTag);
                $this->om->getEntityManager($this->className)->persist($tagObject);
            }

            $tagsCollection->add($tagObject);
        }

        return $tagsCollection;
    }
}