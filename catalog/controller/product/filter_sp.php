<?php
class ControllerProductFilterSP extends Controller
{
	public function index()
	{
		/**
		 * Parts
		 */

		if (isset($this->request->get['path']))
			$parts = explode('_', (string)$this->request->get['path']);
		else $parts = array();

		/**
		 * Service
		 */

		$category_id = end($parts);
		$category_id = $this->model_catalog_category->getCategoryPath($category_id);

		$this->load->model('catalog/category');
		$categories = $this->model_catalog_category->getCategories($category_id);

		foreach ($categories as $category) {
			if ($category['top']) {
				$data['services'][] = array(
					'category_id' => $category['category_id'],
					'name'        => $category['name'],
					'href'        => $this->url->link('product/category', 'path=' . $category['category_id']),
				);
			}
		}

		/**
		 * Region
		 */

		$category_id = end($parts);
		$data['category_id'] = $category_id;

		$this->load->model('catalog/product');
		$product_filters = $this->model_catalog_product->getProductFilters($category_id);

		$data['regions'] = array();

		if ($product_filters){
			foreach ($product_filters as $region) {
				$data['regions'][] = array(
					'region_id' => $region['filter_id'],
					'name'      => $region['name'],
				);
			}
		}

		$data['region'] = 'index.php?route=product/filter_sp/region';
		$data['action'] = 'index.php?route=product/category/more&path=' . $this->request->get['path'];

		return $this->load->view('product/filter_sp', $data);
	}

	public function region()
	{
		$service = isset($this->request->post['service'])? (int)$this->request->post['service']: false;
		$category_id = isset($this->request->post['category_id'])? (int)$this->request->post['category_id']: false;

		$category_id = $service?: $category_id;

		$this->load->model('catalog/product');
		$product_filters = $this->model_catalog_product->getProductFilters($category_id);

		$json['regions'] = array();

		if ($product_filters){
			foreach ($product_filters as $region) {
				$json['regions'][] = array(
					'region_id' => $region['filter_id'],
					'name'      => $region['name'],
				);
			}
		}

		$json['success'] = true;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}