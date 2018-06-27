<?php
class ControllerNewsBlogRelated extends Controller {

	public function index() {
		$this->load->model('newsblog/article');
		$this->load->model('tool/image');
		$images_size_articles_related = array(372, 245);

		if (isset($this->request->get['newsblog_article_id'])) {
			$newsblog_article_id = (int)$this->request->get['newsblog_article_id'];
		} else {
			$newsblog_article_id = 0;
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['articles'] = array();

		$filter_data = array(
			'start' => ($page - 1) * 3,
			'limit' => 3
		);

		$results = $this->model_newsblog_article->getArticleRelated($newsblog_article_id, $filter_data);
		$article_total = $this->model_newsblog_article->getTotalArticleRelated($newsblog_article_id, $filter_data);

		foreach ($results as $result) {

			if ($result['image']) {
				$original 	= HTTP_SERVER.'image/'.$result['image'];
				$thumb = $this->model_tool_image->resize($result['image'], $images_size_articles_related[0], $images_size_articles_related[1]);
			} else {
				$original = false;
				$thumb = false;
			}

			$mainCategoryId =  $this->model_newsblog_article->getArticleMainCategoryId($result['article_id']);

			$data['articles'][] = array(
					'article_id'  		=> $result['article_id'],
					'original'			=> $original,
					'thumb'       		=> $thumb,
					'name'        		=> $result['name'],
					'preview'     		=> html_entity_decode($result['preview'], ENT_QUOTES, 'UTF-8'),
					'attributes'  		=> $result['attributes'],
					'href'        		=> $this->url->link('newsblog/article', 'newsblog_path=' . $mainCategoryId . '&newsblog_article_id=' . $result['article_id']),
					'viewed' 			=> $result['viewed']
			);
		}

		$pagination = new Pagination();
		$pagination->total = (int)$article_total;
		$pagination->page = $page;
		$pagination->limit = 3;
		$pagination->url = $this->url->link('newsblog/related', 'newsblog_path=' . $this->request->get['newsblog_path'] . '&page={page}');

		$json['next'] = $pagination->render('next');
		$json['articles'] = $this->load->view('newsblog/related', $data);
		$json['success'] = true;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

}