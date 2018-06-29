<?php
class ControllerTestimonialRating extends Controller
{
	public function index()
	{
   	    $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['heading_title'] = 'Рейтинги';
        $this->document->setTitle($data['heading_title']);

        $data['breadcrumbs'][] = array(
            'text' => $data['heading_title'],
            'href' => $this->url->link('testimonial/rating')
        );

        $url = 'index.php?route=testimonial/rating/rating';
        $data['top_5'] = $url;
        $data['top_10'] = $url . '&limit=10';
        $data['top_100'] = $url . '&limit=100';

        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $data['enterprises_ratings'] = $this->load->controller('testimonial/enterprises_ratings');

        $this->response->setOutput($this->load->view('testimonial/rating', $data));
	}

	public function rating()
	{
		$this->load->model('extension/module/testimonial');

        if (isset($this->request->get['limit']))
			$limit = (int)$this->request->get['limit'];
		else $limit = 5;
        $results = $this->model_extension_module_testimonial->getRatings($limit);

		$data['ratings'] = array();

        foreach ($results as $result) {
            $data['ratings'][] = array(
                'name'   => $result['name'],
                'rating' => number_format($result['rating'], 1, ',', ''),
                'review' => (int)$result['review'],
                'ceil'   => ceil($result['rating'])
            );
        }

        $json['rating'] = $this->load->view('testimonial/rating_list', $data);
        $json['success'] = true;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
	}
}