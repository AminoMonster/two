<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

namespace Tygh\Api\Entities;

use Tygh\Api\AEntity;
use Tygh\Api\Response;
use Tygh\Registry;

class Products extends AEntity
{
    public function index($id = 0, $params = array())
    {
        $lang_code = $this->_safeGet($params, 'lang_code', DEFAULT_LANGUAGE);

        if ($this->getParentName() == 'categories') {
            $parent_category = $this->getParentData();
            $params['cid'] = $parent_category['category_id'];
        }

        if (!empty($id)) {
            $data = fn_get_product_data($id, $this->auth, $lang_code, '', true, true, true, false, false, false, false);

            if (empty($data)) {
                $status = Response::STATUS_NOT_FOUND;
            } else {
                $status = Response::STATUS_OK;
            }

        } else {
            $params['items_per_page'] = $this->_safeGet($params, 'items_per_page', Registry::get('settings.Appearance.admin_products_per_page'));
            $params['extend'][] = 'categories';
            list($products, $search) = fn_get_products($params, 0, $lang_code);

            $params['get_options'] = $this->_safeGet($params, 'get_options', false);
            $params['get_features'] = $this->_safeGet($params, 'get_features', true);
            $params['get_detailed'] = $this->_safeGet($params, 'get_detailed', true);
            $params['get_icon'] = $this->_safeGet($params, 'get_icon', true);
            $params['get_additional'] = $this->_safeGet($params, 'get_additional', true);
            $params['detailed_params'] = $this->_safeGet($params, 'detailed_params', false);
            $params['features_display_on'] = 'A';

            fn_gather_additional_products_data($products, $params);

            $data = array(
                'products' => $products,
                'params' => $search
            );
            $status = Response::STATUS_OK;
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function create($params)
    {
        $data = array();
        $valid_params = true;
        $status = Response::STATUS_BAD_REQUEST;
        unset($params['product_id']);

        if (empty($params['category_ids'])) {
            fn_set_notification('E', __('error'), __('category_is_empty'));
            $valid_params = false;
        } elseif (!is_array($params['category_ids'])) {
            fn_set_notification('E', __('error'), __('api_products_is_array_category_ids'));
            $valid_params = false;
        }

        if ($valid_params) {
            $this->prepareFeature($params);
            $this->prepareImages($params);
            $product_id = fn_update_product($params);

            if ($product_id) {
                $status = Response::STATUS_CREATED;
                $data = array(
                    'product_id' => $product_id,
                );
            }
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function update($id, $params)
    {
        $data = array();
        $status = Response::STATUS_BAD_REQUEST;

        $lang_code = $this->_safeGet($params, 'lang_code', DEFAULT_LANGUAGE);
        $this->prepareFeature($params);
        $this->prepareImages($params, $id);
        $product_id = fn_update_product($params, $id, $lang_code);

        if ($product_id) {
            $status = Response::STATUS_OK;
            $data = array(
                'product_id' => $product_id
            );
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function delete($id)
    {
        $data = array();
        $status = Response::STATUS_BAD_REQUEST;

        if (fn_delete_product($id)) {
            $status = Response::STATUS_OK;
            $data['message'] = 'Ok';
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function priveleges()
    {
        return array(
            'create' => 'manage_catalog',
            'update' => 'manage_catalog',
            'delete' => 'manage_catalog',
            'index'  => 'view_catalog'
        );
    }

    public function childEntities()
    {
        return array(
            'features'
        );
    }

    public function prepareImages($params, $product_id = 0)
    {
        if (isset($params['main_pair'])) {

            if ($product_id != 0) {
                $products_images = fn_get_image_pairs($product_id, 'product', 'M', true, true, DEFAULT_LANGUAGE);
                if (!empty($products_images)) {
                    fn_delete_image_pair($products_images['pair_id']);
                }
            }

            if (!empty($params['main_pair']['detailed']['image_path'])) {
                $_REQUEST['file_product_main_image_detailed'][] = $params['main_pair']['detailed']['image_path'];
                $_REQUEST['type_product_main_image_detailed'][] = (strpos($params['main_pair']['detailed']['image_path'], '://') === false) ? 'server' : 'url';
            }

            if (!empty($params['main_pair']['icon']['image_path'])) {
                $_REQUEST['file_product_main_image_icon'][] = $params['main_pair']['icon']['image_path'];
                $_REQUEST['type_product_main_image_icon'][] = (strpos($params['main_pair']['icon']['image_path'], '://') === false) ? 'server' : 'url';
            }

            $_REQUEST['product_main_image_data'][] = array(
                'pair_id' => 0,
                'type' => 'M',
                'object_id' => 0,
                'image_alt' => !empty($params['main_pair']['icon']['alt']) ? $params['main_pair']['icon']['alt'] : '',
                'detailed_alt' => !empty($params['main_pair']['detailed']['alt']) ? $params['main_pair']['detailed']['alt'] : '',
            );
        }

        if (isset($params['image_pairs'])) {

            if ($product_id != 0) {
                $additional_images = fn_get_image_pairs($product_id, 'product', 'A', true, true, DEFAULT_LANGUAGE);
                foreach ($additional_images as $pair) {
                    fn_delete_image_pair($pair['pair_id']);
                }
            }

            foreach ($params['image_pairs'] as $pair_id => $pair) {

                if (!empty($pair['icon']['image_path'])) {
                    $_REQUEST['file_product_add_additional_image_icon'][] = $pair['icon']['image_path'];
                    $_REQUEST['type_product_add_additional_image_icon'][] = (strpos($pair['icon']['image_path'], '://') === false) ? 'server' : 'url';
                }

                if (!empty($pair['detailed']['image_path'])) {
                    $_REQUEST['file_product_add_additional_image_detailed'][] = $pair['detailed']['image_path'];
                    $_REQUEST['type_product_add_additional_image_detailed'][] = (strpos($pair['detailed']['image_path'], '://') === false) ? 'server' : 'url';
                }

                $_REQUEST['product_add_additional_image_data'][] = array(
                    'position' => !empty($pair['position']) ? $pair['position'] : 0,
                    'pair_id' => 0,
                    'type' => 'A',
                    'object_id' => 0,
                    'image_alt' => !empty($pair['icon']['alt']) ? $pair['icon']['image_path'] : '',
                    'detailed_alt' => !empty($pair['detailed']['alt']) ? $pair['icon']['image_path'] : '',
                );
            }
        }
    }

    public function prepareFeature(&$params)
    {
        if (!empty($params['product_features'])) {
            $features = $params['product_features'];
            $params['product_features'] = array();

            foreach ($features as $feature_id => $feature) {
                if (!empty($feature['feature_type'])) {
                    if (strpos('SNE', $feature['feature_type']) !== false) {
                        $params['product_features'][$feature_id] = $feature['variant_id'];

                    } elseif (strpos('OD', $feature['feature_type']) !== false) {
                        $params['product_features'][$feature_id] = $feature['value_int'];

                    } elseif (strpos('M', $feature['feature_type']) !== false) {
                        foreach ($feature['variants'] as $variant) {
                            $params['product_features'][$feature_id][] = $variant['variant_id'];
                        }

                    } else { // CT
                        $params['product_features'][$feature_id] = $feature['value'];
                    }

                } else {
                    $params['product_features'][$feature_id] = $feature;
                }
            }
        }
    }
}
