<?php
class ControllerExtensionModuleCategory extends Controller {
	public function index() {
		$this->load->language('extension/module/category');

		if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
		} else {
			$parts = array();
		}

		if (isset($parts[0])) {
			$data['category_id'] = $parts[0];
		} else {
			$data['category_id'] = 0;
		}

		if (isset($parts[1])) {
			$data['child_id'] = $parts[1];
		} else {
			$data['child_id'] = 0;
		}

		$this->load->model('catalog/category');

		$data['categories'] = array();

		$category_top = $this->model_catalog_category->getCategory(65);
		$data['category_top'] = [
			'name' => $category_top['name'],
		];

		$categories = $this->model_catalog_category->getCategories(65);

		foreach ($categories as $category) {
			if ($category['top']) {
				$data['categories'][] = array(
					'image' => '/image/' . $category['image'],
					'name'     => $category['name'],
					'href'     => $this->url->link('product/category', 'path=' . $category['category_id']),
				);
			}
		}

		return $this->load->view('extension/module/category', $data);
	}
}