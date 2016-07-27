<?php

namespace Eight\PageBundle\Widget;

interface WidgetInterface
{
    public function getVars();

    public function getLayout();

    public function getName();

    public function js();

    public function css();
}