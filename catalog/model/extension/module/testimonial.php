<?php

class ModelExtensionModuleTestimonial extends Model
{
    public function addReview($data)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "review SET author = '" . $this->db->escape($data['name']) . "', email = '" . $this->db->escape($data['email']) . "', customer_id = '" . (int)$this->customer->getId() . "', product_id = '" . (int)$data['product_id'] . "', text = '" . $this->db->escape($data['text']) . "', rating = '" . (int)$data['rating'] . "', date_added = NOW()");

        $review_id = $this->db->getLastId();

        $this->load->language('extension/module/testimonial');

        $subject = sprintf($this->language->get('text_mail_subject'), $this->config->get('config_name'));

        $message = $this->language->get('text_mail_waiting') . "\n";
        $message .= sprintf($this->language->get('text_mail_author'), $this->db->escape(strip_tags($data['name']))) . "\n";
        if ($data['rating']) {
            $message .= sprintf($this->language->get('text_mail_rating'), $this->db->escape(strip_tags($data['rating']))) . "\n";
        }
        $message .= $this->language->get('text_mail_text') . "\n";
        $message .= $this->db->escape(strip_tags($data['text'])) . "\n\n";

        if(substr(VERSION, 0, 7) < '2.0.2.0'){
            $mail = new Mail($this->config->get('config_mail'));
        } else {
            $mail = new Mail();
            $mail->protocol = $this->config->get('config_mail_protocol');
            $mail->parameter = $this->config->get('config_mail_parameter');
            $mail->smtp_hostname = $this->config->get('config_mail_smtp_host')?$this->config->get('config_mail_smtp_host'):$this->config->get('config_mail_smtp_hostname');
            $mail->smtp_username = $this->config->get('config_mail_smtp_username');
            $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
            $mail->smtp_port = $this->config->get('config_mail_smtp_port');
            $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
        }

        $mail->setTo($this->config->get('config_email'));
        $mail->setFrom($this->config->get('config_email'));
        $mail->setSender($this->config->get('config_name'));
        $mail->setSubject($subject);
        $mail->setText($message);

        $mail_list = '';

        if(substr(VERSION, 0, 7) > '2.2.0.0'){
            if(in_array('testimonial', (array)$this->config->get('config_mail_alert'))){
                $mail->send();
                $mail_list = $this->config->get('config_mail_alert_email');
            }
        } else if($this->config->get('config_review_mail')){
            $mail->send();
            $mail_list = $this->config->get('config_mail_alert');
        }
        if($mail_list && !is_array($mail_list)) {
            $emails = explode(',', $mail_list);
            foreach ($emails as $email) {
                if ($email && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
                    $mail->setTo($email);
                    $mail->send();
                }
            }
        }
    }

    public function getReviews($start = 0, $limit = 20)
    {
        if ($start < 0) {
            $start = 0;
        }

        if ($limit < 1) {
            $limit = 20;
        }

        $query = $this->db->query("SELECT r.review_id, r.customer_id, r.author, r.rating, r.text, r.date_added FROM " . DB_PREFIX . "review r WHERE r.product_id >= '0'  AND r.status = '1'  ORDER BY r.date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

        return $query->rows;
    }

    public function getModuleReviews($start = 0, $limit = 20, $order = 0, $product_id = 0)
    {
        if ($start < 0) {
            $start = 0;
        }

        if ($limit < 1) {
            $limit = 20;
        }
        switch ($order) {
            case 0: {
                $sql = "ORDER BY date_added  DESC LIMIT " . (int)$start . "," . (int)$limit;
                break;
            }
            case 1: {
                $sql = "ORDER BY RAND() DESC LIMIT " . (int)$start . "," . (int)$limit;
                break;
            }
            default: {
                $sql = "ORDER BY date_added DESC LIMIT " . (int)$start . "," . (int)$limit;
            }
        }

        if ($product_id)
            $product_sql = "product_id = '" . (int)$product_id . "'";
        else $product_sql = "product_id >= '0'";

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "review WHERE " . $product_sql . "  AND status = '1'  " . $sql);

        return $query->rows;
    }

    public function getTotalReviews()
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r WHERE r.product_id >= '0' AND r.status = '1'");

        return $query->row['total'];
    }

    public function getProducts() {
        $sql = "SELECT p.product_id, pd.name FROM " . DB_PREFIX . "product p";
        $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
        $sql .= " GROUP BY p.product_id";
        $sql .= " ORDER BY p.sort_order";
        $sql .= " ASC, LCASE(pd.name) ASC";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getRatings($limit = 5)
    {
        $sql = "SELECT p.product_id, pd.name, AVG(rw.rating) as rating, COUNT(rw.product_id) as review FROM " . DB_PREFIX . "product p";
        $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "review rw ON (p.product_id = rw.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND rw.product_id = p.product_id AND rw.status = '1'";
        $sql .= " GROUP BY p.product_id";
        $sql .= " ORDER BY rating DESC";
        $sql .= " LIMIT " . $limit;

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getEnterprisesRating($start = 0, $limit = 20)
    {
        if ($start < 0) $start = 0;
        if ($limit < 1) $limit = 20;

        $sql = "SELECT p.product_id, pd.name, p.image, AVG(rw.rating) as rating, COUNT(rw.product_id) as review FROM " . DB_PREFIX . "product p";
        $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "review rw ON (p.product_id = rw.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND rw.product_id = p.product_id AND rw.status = '1'";
        $sql .= " GROUP BY p.product_id";
        $sql .= " ORDER BY rating DESC";
        $sql .= " LIMIT " . (int)$start . "," . (int)$limit;

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalEnterprisesRating()
    {
        $query = $this->db->query("SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "review rw ON (p.product_id = rw.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND rw.product_id = p.product_id AND rw.status = '1'");

        return $query->row['total'];
    }

}