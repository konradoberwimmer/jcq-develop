<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * JCQ Component Administrator Controller
 */
class JCQAdminController extends JController
{

	/**
	 * Method to display the view
	 *
	 * @access    public
	 */
	function display()
	{
		//This sets the default view (second argument)
		$viewName    = JRequest::getVar( 'view', 'projectlist' );
		//This sets the default layout/template for the view
		$viewLayout  = JRequest::getVar( 'layout', 'projectlistlayout' );

		$view = & $this->getView($viewName);
			
		// Get/Create the model
		if ($model = & $this->getModel('jcq')) {
			//Push the model into the view (as default)
			//Second parameter indicates that it is the default model for the view
			$view->setModel($model, true);
		}

		$view->setLayout($viewLayout);
		$view->display();

	}
}