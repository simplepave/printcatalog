<?php

class ControllerExtensionModuleTestimonial extends Controller
{
    private $error = array();
    private $moduleName 			= 'testimonial';
    private $moduleModel 			= 'model_extension_module_testimonial';
    private $modulePath 			= 'extension/module/testimonial';
    private $moduleVersion 			= '1.4.2';

    public function index() {

        $lang_ar = $this->load->language($this->modulePath);

        foreach($lang_ar as $key => $item){
            $data[$key] = $item;
        }

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/module');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if (!isset($this->request->get['module_id'])) {
                $this->model_setting_module->addModule($this->moduleName, $this->request->post);
                $module_id =  $this->db->getLastId();
            } else {
                $this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
                $module_id =  $this->request->get['module_id'];
            }

            $this->session->data['success'] = $this->language->get('text_success');

            if(!$this->request->post['apply']){
                $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL'));
            }else{
                $this->response->redirect($this->url->link($this->modulePath, 'user_token='. $this->session->data['user_token'] . '&module_id=' . $module_id , 'SSL'));
            }
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL')
        );

        if (!isset($this->request->get['module_id'])) {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'). ' ' .$this->moduleVersion,
                'href' => $this->url->link($this->modulePath, 'user_token=' . $this->session->data['user_token'], 'SSL')
            );
        } else {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'). ' ' .$this->moduleVersion,
                'href' => $this->url->link($this->modulePath, 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], 'SSL')
            );
        }

        if (!isset($this->request->get['module_id'])) {
            $data['action'] = $this->url->link($this->modulePath, 'user_token=' . $this->session->data['user_token'], 'SSL');
        } else {
            $data['action'] = $this->url->link($this->modulePath, 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], 'SSL');
        }

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL');

        $data['notification'] = $this->url->link($this->modulePath . '/getNotification', 'user_token=' . $this->session->data['user_token'], 'SSL');

        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
        }

        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } elseif (!empty($module_info)) {
            $data['name'] = $module_info['name'];
        } else {
            $data['name'] = '';
        }

        if (isset($this->request->post['order'])) {
            $data['order'] = $this->request->post['order'];
        } elseif (!empty($module_info)) {
            $data['order'] = $module_info['order'];
        } else {
            $data['order'] = '';
        }

        $data['user_token'] = $this->session->data['user_token'];


        if (isset($this->request->post['layout_id'])) {
            $data['layout_id'] = $this->request->post['layout_id'];
        } elseif (!empty($module_info)) {
            $data['layout_id'] = $module_info['layout_id'];
        } else {
            $data['layout_id'] = '';
        }

        if (isset($this->request->post['limit'])) {
            $data['limit'] = $this->request->post['limit'];
        } elseif (!empty($module_info)) {
            $data['limit'] = $module_info['limit'];
        } else {
            $data['limit'] = '3';
        }

        if (isset($this->request->post['text_limit'])) {
            $data['text_limit'] = $this->request->post['text_limit'];
        } elseif (!empty($module_info)) {
            $data['text_limit'] = $module_info['text_limit'];
        } else {
            $data['text_limit'] = '100';
        }

        if (isset($this->request->post['button_all'])) {
            $data['button_all'] = $this->request->post['button_all'];
        } elseif (!empty($module_info)) {
            $data['button_all'] = $module_info['button_all'];
        } else {
            $data['button_all'] = '1';
        }

        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($module_info)) {
            $data['status'] = $module_info['status'];
        } else {
            $data['status'] = '1';
        }

        if (isset($this->request->post['module_description'])) {
            $data['module_description'] = $this->request->post['module_description'];
        } elseif (!empty($module_info)) {
            $data['module_description'] = $module_info['module_description'];
        } else {
            $data['module_description'] = '';
        }

        $this->load->model('localisation/language');

        $data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view($this->modulePath, $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', $this->modulePath)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
            $this->error['name'] = $this->language->get('error_name');
        }

        return !$this->error;
    }

    public function install() {
        $this->load->model($this->modulePath);
        $result = $this->{$this->moduleModel}->install();
        if($result->num_rows){
            $modifications = $this->load->controller('extension/modification/refresh');
        }
    }

    public function uninstall() {
        $this->load->model($this->modulePath);
        $result = $this->{$this->moduleModel}->uninstall();
        if($result->num_rows){
            $modifications = $this->load->controller('extension/modification/refresh');
        }
    }

    public function getNotification() {
        sleep(1);
        $this->load->language($this->modulePath);

        $json['version'] = $this->moduleVersion;

        $language_code = $this->config->get('config_admin_language');
        $result = $this->getCurlContent("http://vanstudio.co.ua/index.php?route=information/message&type=module_testimonial_lite&version=$this->moduleVersion&language_code=$language_code");


        if (stripos($result,'<html') !== false || $result == false) {
            $json['message'] = '';
            $json['error'] = $this->language->get('error_notification');
        } else {
            $json['message'] = $result;
            $json['error'] = '';
        }

        $this->response->setOutput(json_encode($json));
    }

    protected function getCurlContent($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
}