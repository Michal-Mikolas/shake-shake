<?php
namespace Shake\Application\UI;

use \Shake\Utils\Strings,
	\Shake\VisualPaginator;
use \Nette;


/**
 * Presenter
 *
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 */
class Presenter extends Nette\Application\UI\Presenter
{

	public function getEntityName()
	{
		$name = $this->name;                                    // Admin:ShopCategory
		$name = trim(substr($name, strrpos($name, ':')), ':');  // ShopCategory
		$name = Strings::toCamelCase($name);                    // shopCategory

		return $name;
	}



	public function getListName()
	{
		return Strings::plural( $this->getEntityName() );
	}



	public function getPaginatedListName()
	{
		return $this->getListName() . 'Paginated';
	}



	public function getRepositoryName()
	{
		return $this->getEntityName() . 'Repository';
	}



	public function getServiceName()
	{
		return $this->getEntityName() . 'Service';
	}



	public function renderDefault()
	{
		$this->template->{$this->listName} = $this->context->{$this->serviceName}->getList();

		$data = $this->context->{$this->serviceName}->getList();
		$this['visualPaginator']->paginator->itemCount = $this->context->{$this->serviceName}->count($data);
		$this->template->{$this->paginatedListName} = $this->paginate(
			$data,
			$this->context->{$this->serviceName}
		);
	}



	public function renderDetail($id)
	{
		$this->template->{$this->entityName} = $this->context->{$this->serviceName}->get($id);
	}



	public function renderEdit($id)
	{
		$entry = $this->context->{$this->serviceName}->get($id);
		
		$this->template->{$this->entityName} = $entry;
		$this['form']->setDefaults($entry);
	}



	public function renderAdd()
	{
	}



	public function handleDelete($id)
	{
		$result = $this->context->{$this->serviceName}->delete($id);

		if ($result) {
			$this->flashMessage('Entry succesfully deleted.');
		} else {
			$this->flashMessage('No data was deleted.', 'error');			
		}

		$this->redirect('this');
	}



	protected function createComponentForm($name)
	{
		/** @todo */
	}



	public function processForm(Nette\Application\UI\Form $form)
	{
		$values = $form->values;

		// Edit
		if ($id = $this->getParam('id')) {
			$this->context->{$this->serviceName}->update($id, $values);
			$this->flashMessage('Entry successfully updated.');

		// Create
		} else {
			$this->context->{$this->serviceName}->create($values);
			$this->flashMessage('Entry successfully created.');
		}

		$this->redirect('this');
	}



	protected function createComponentVisualPaginator($name)
	{
		$vp = new VisualPaginator($this, $name);
		$vp->paginator->itemsPerPage = 10;

		return $vp;
	}



	protected function paginate($data, $repository)
	{
		return $repository->applyLimit(
			$data, 
			$this['visualPaginator']->paginator->itemsPerPage, 
			$this['visualPaginator']->paginator->offset
		);
	}



	public function formatTemplateFiles()
	{
		$name = $this->getName();		
		$modules = explode(':', $name);
		$presenter = array_pop($modules);

		$dir = $this->context->parameters['appDir'];
		foreach ($modules as $module) {
			$dir .= "/{$module}Module";
		}

		return array(
			"$dir/templates/$presenter/$this->view.latte",
			"$dir/templates/$presenter.$this->view.latte",
			"$dir/templates/$presenter/$this->view.phtml",
			"$dir/templates/$presenter.$this->view.phtml",
		);
	}



	public function formatLayoutTemplateFiles()
	{
		$name = $this->getName();		
		$modules = explode(':', $name);
		$presenter = array_pop($modules);
		$layout = $this->layout ? $this->layout : 'layout';

		$dir = $this->context->parameters['appDir'];
		foreach ($modules as $module) {
			$dir .= "/{$module}Module";
		}
		
		$list = array(
			"$dir/templates/$presenter/@$layout.latte",
			"$dir/templates/$presenter.@$layout.latte",
			"$dir/templates/$presenter/@$layout.phtml",
			"$dir/templates/$presenter.@$layout.phtml",
		);
		do {
			$list[] = "$dir/templates/@$layout.latte";
			$list[] = "$dir/templates/@$layout.phtml";
			$dir = dirname($dir);
		} while ($dir && ($name = substr($name, 0, strrpos($name, ':'))));
		
		return $list;
	}

}