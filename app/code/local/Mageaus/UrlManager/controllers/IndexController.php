<?php

/**
 * Maho
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 * @copyright  Copyright (c) 2025 Maho (https://mahocommerce.com)
 */

/**
 * Frontend Index Controller
 *
 * @category   Mageaus
 * @package    Mageaus_UrlManager
 */
class Mageaus_UrlManager_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Index action - list all items
     */
    public function indexAction(): void
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');

        $this->getLayout()->getBlock('head')->setTitle(
            $this->__('Redirect'),
        );

        // Add breadcrumbs
        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb('home', [
                'label' => $this->__('Home'),
                'title' => $this->__('Go to Home Page'),
                'link' => Mage::getBaseUrl(),
            ]);
            $breadcrumbs->addCrumb('redirect', [
                'label' => $this->__('Redirect'),
                'title' => $this->__('Redirect'),
            ]);
        }

        $this->renderLayout();
    }

    /**
     * View action - view single item
     */
    public function viewAction(): void
    {
        $id = $this->getRequest()->getParam('id');
        $redirect = Mage::getModel('mageaus_urlmanager/redirect')->load($id);

        if (!$redirect->getId()) {
            $this->norouteAction();
            return;
        }

        Mage::register('current_redirect', $redirect);

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');

        // Set page title
        $title = $redirect->getName() ?? $redirect->getTitle() ?? 'View Redirect';
        $this->getLayout()->getBlock('head')->setTitle($title);

        // Add breadcrumbs
        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb('home', [
                'label' => $this->__('Home'),
                'title' => $this->__('Go to Home Page'),
                'link' => Mage::getBaseUrl(),
            ]);
            $breadcrumbs->addCrumb('redirect', [
                'label' => $this->__('Redirect'),
                'title' => $this->__('Redirect'),
                'link' => Mage::getUrl('*/*'),
            ]);
            $breadcrumbs->addCrumb('view', [
                'label' => $title,
                'title' => $title,
            ]);
        }

        $this->renderLayout();
    }
}
