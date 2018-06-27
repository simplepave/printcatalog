<?php
class ControllerNewsBlogMore extends Controller {

	public function index() {
		$this->load->language('newsblog/category');

		$this->load->model('newsblog/category');
		$this->load->model('newsblog/article');

		$this->load->model('tool/image');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['newsblog_path'])) {
			$parts = explode('_', (string)$this->request->get['newsblog_path']);
			$category_id = (int)array_pop($parts);
		}
		else $category_id = 0;

		$category_info = $this->model_newsblog_category->getCategory($category_id);

		if ($category_info) {

			if ($category_info['settings']) {
				$settings = unserialize($category_info['settings']);
	         $category_info = array_merge($category_info, $settings);
            $date_format = $settings['date_format'];
         }

         $articles_image_size = array(372, 245);
			$data['articles'] = array();
			$limit = $category_info['limit'];

			if ($limit > 0) {

				$sort = $category_info['sort_by'];
				$order = $category_info['sort_direction'];

				$filter_data = array(
					'filter_category_id' => $category_id,
					'sort'               => $sort,
					'order'              => $order,
					'start'              => ($page - 1) * $limit,
					'limit'              => $limit
				);

				$article_total = $this->model_newsblog_article->getTotalArticles($filter_data);
				$results = $this->model_newsblog_article->getArticles($filter_data);

				foreach ($results as $result) {

					if ($result['image']) {
						$original 	= HTTP_SERVER.'image/'.$result['image'];
						$thumb 		= $this->model_tool_image->resize($result['image'], $articles_image_size[0], $articles_image_size[1]);
					} else {
						$original 	= '';
						$thumb 		= '';	//or use 'placeholder.png' if you need
					}

					$data['articles'][] = array(
						'article_id'  		=> $result['article_id'],
						'original'			=> $original,
						'thumb'       		=> $thumb,
						'name'        		=> $result['name'],
						'preview'     		=> html_entity_decode($result['preview'], ENT_QUOTES, 'UTF-8'),
						'attributes'  		=> $result['attributes'],
						'href'        		=> $this->url->link('newsblog/article', 'newsblog_path=' . $this->request->get['newsblog_path'] . '&newsblog_article_id=' . $result['article_id']),
						'viewed' 			=> $result['viewed']
					);
				}

				$pagination = new Pagination();
				$pagination->total = $article_total;
				$pagination->page = $page;
				$pagination->limit = $limit;
				$pagination->url = $this->url->link('newsblog/more', 'newsblog_path=' . $this->request->get['newsblog_path'] . '&page={page}');

				$json['next'] = $pagination->render('next');
				$json['articles'] = $this->load->view('newsblog/more', $data);
				$json['success'] = true;
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}