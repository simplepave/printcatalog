<?php
class ControllerTestimonialEnterprisesRatings extends Controller
{
	public function index()
	{
		$this->load->model('extension/module/testimonial');
		$this->load->model('tool/image');

		$data['heading_title'] = 'Предприятия с высокими рейтингами';

		$results = $this->model_extension_module_testimonial->getEnterprisesRating((1 - 1) * 6, 6);

		$data['ratings'] = array();

		foreach ($results as $result) {
		   $data['ratings'][] = array(
		      'name'   => $result['name'],
		      'image'  => HTTP_SERVER.'image/' . $result['image'],
		      'rating' => number_format($result['rating'], 1, ',', ''),
		      'review' => (int)$result['review'],
		      'ceil'   => ceil($result['rating'])
		   );
		}

		$total = $this->model_extension_module_testimonial->getTotalEnterprisesRating();

		$pagination = new Pagination();
		$pagination->total = (int)$total;
		$pagination->page = 1;
		$pagination->limit = 6;
		$pagination->url = 'index.php?route=testimonial/enterprises_ratings/more&page={page}';
		$data['next'] = $pagination->render('next');

		return $this->load->view('testimonial/enterprises_ratings', $data);
	}

	public function more()
	{
		$this->load->model('extension/module/testimonial');
		$this->load->model('tool/image');

		if (isset($this->request->get['page']))
			$page = $this->request->get['page'];
		else $page = 1;

		$results = $this->model_extension_module_testimonial->getEnterprisesRating(($page - 1) * 6, 6);

		$data['ratings'] = array();

		foreach ($results as $result) {
		   $data['ratings'][] = array(
		      'name'   => $result['name'],
		      'image'  => HTTP_SERVER.'image/' . $result['image'],
		      'rating' => number_format($result['rating'], 1, ',', ''),
		      'review' => (int)$result['review'],
		      'ceil'   => ceil($result['rating'])
		   );
		}

		$total = $this->model_extension_module_testimonial->getTotalEnterprisesRating();

		$pagination = new Pagination();
		$pagination->total = (int)$total;
		$pagination->page = $page;
		$pagination->limit = 6;
		$pagination->url = 'index.php?route=testimonial/enterprises_ratings/more&page={page}';

		$json['next'] = $pagination->render('next');
		$json['enterprises'] = $this->load->view('testimonial/enterprises_ratings_list', $data);
		$json['success'] = true;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}