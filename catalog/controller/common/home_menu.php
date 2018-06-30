<?php
class ControllerCommonHomeMenu extends Controller {
	public function index() {

		$this->load->model('catalog/category');
		$this->load->model('tool/image');

		$data['categories'] = array();

		$category_top = $this->model_catalog_category->getCategory(65);
		$data['category_top'] = [
			'name' => $category_top['name'],
			'href' => $this->url->link('product/category', 'path=' . $category_top['category_id']),
		];

		$categories = $this->model_catalog_category->getCategories(65);

		foreach ($categories as $category) {
			if ($category['top']) {
				$description = html_entity_decode($category['description'], ENT_QUOTES, 'UTF-8');

				if (preg_match( '/<!--home(.*?)?-->/', $description, $matches)) {
					$description_arr = explode($matches[0], $description, 2);
					$description = $description_arr[0] . '</ul>';
				}
				else $description = false;

				$data['categories'][] = array(
					'image' => '/image/' . $category['image'],
					'name'     => $category['name'],
					'href'     => $this->url->link('product/category', 'path=' . $category['category_id']),
					'description' => $description,
				);
			}
		}

		return $this->load->view('common/home_menu', $data);
	}
}