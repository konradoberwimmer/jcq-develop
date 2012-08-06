<?php
abstract class vwadmin
{
	public abstract function doTask();
	public abstract function show();
	protected abstract function breadcrumb();
		
	public function showBreadcrumbs()
	{
		JToolBarHelper::title($this->breadcrumb());
	}
}