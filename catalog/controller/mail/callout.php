<?php
class ControllerMailCallout extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('mail/callout');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (isset($this->request->post['enquiry'])) {
				$data['heder'] = sprintf($this->language->get('text_order'), $this->request->post['name']);
				$data['enquiry'] = $this->request->post['enquiry'];
			} else

			$data['heder'] = sprintf($this->language->get('text_order'), $this->request->post['name']);
			$data['telephone'] = sprintf($this->language->get('text_telephone'), $this->request->post['telephone']);
			$data['ip'] = $this->request->server['REMOTE_ADDR'];


			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->request->post['name'], ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode(sprintf($this->language->get('email_subject'), $this->request->post['name']), ENT_QUOTES, 'UTF-8'));
			$mail->setText($this->load->view('mail/callout', $data));
			$mail->send();

			$json['text_success'] = $this->language->get('text_success');
			$json['success'] = true;
		}
		else {
			if (isset($this->error['name']))
				$json['error_name'] = $this->error['name'];

			if (isset($this->error['telephone']))
				$json['error_telephone'] = $this->error['telephone'];

			if (isset($this->error['enquiry']))
				$json['error_enquiry'] = $this->error['enquiry'];

			$json['error'] = true;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	protected function validate() {
		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 32)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
		}

		if (isset($this->request->post['enquiry'])) {
			if ((utf8_strlen($this->request->post['enquiry']) < 10) || (utf8_strlen($this->request->post['enquiry']) > 3000)) {
				$this->error['enquiry'] = $this->language->get('error_enquiry');
			}
		}

		return !$this->error;
	}
}
