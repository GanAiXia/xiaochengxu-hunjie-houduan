<?php

//decode by http://www.yunlu99.com/
class form_element
{
	public static function textarea($name, $id = '', $value = '', $rows = 3, $cols = 70, $class = '', $ext = '')
	{
		if (!$id) {
			$id = $name;
		}
		return "<textarea name=\"{$name}\" id=\"{$id}\" rows=\"{$rows}\" cols=\"{$cols}\" class=\"{$class}\" {$ext}>{$value}</textarea>";
	}
	public static function select($options, $name, $id = '', $value = '', $size = 1, $class = '', $ext = '', $tips = '')
	{
		if (!$id) {
			$id = $name;
		}
		if (!is_array($options)) {
			$options = self::_option($options);
		}
		if ($size >= 1) {
			$size = " size=\"{$size}\"";
		}
		if ($class) {
			$class = " class=\"{$class}\"";
		}
		$tips = $tips ? $tips : "请选择…";
		$data .= "<select name=\"{$name}\" id=\"{$id}\" {$size} {$class} {$ext}><option value=\"\">{$tips}</option>";
		foreach ($options as $k => $v) {
			$selected = $k == $value ? "selected" : '';
			$data .= "<option value=\"{$k}\" {$selected}>{$v}</option>\n";
		}
		$data .= "</select>";
		return $data;
	}
	function _option($options, $s1 = ",", $s2 = "|")
	{
		if (empty($options)) {
			return null;
		}
		$options = explode($s1, $options);
		foreach ($options as $option) {
			if (strpos($option, $s2)) {
				list($name, $value) = explode($s2, trim($option));
			} else {
				$name = $value = trim($option);
			}
			$os[$value] = $name;
		}
		return $os;
	}
	public static function checkbox($options, $name, $id = '', $value = '', $cols = 5, $class = '', $ext = '', $width = 100)
	{
		if (!$options) {
			return '';
		}
		if (!$id) {
			$id = $name;
		}
		if (!is_array($options)) {
			$options = self::_option($options);
		}
		$i = 1;
		$data = "<input type=\"hidden\" name=\"" . $name . "\" value=\"-99\">";
		if ($class) {
			$class = " class=\"{$class}\"";
		}
		if ($value != '') {
			$value = strpos($value, ",") ? explode(",", $value) : array($value);
		}
		foreach ($options as $k => $v) {
			$checked = $value && in_array($v, $value) ? "checked" : '';
			$data .= "<span class=\"ckspan\"><label><input class=\"ck_style\" type=\"checkbox\" boxid=\"{$id}\" name=\"{$name}[]\" id=\"{$id}\" value=\"{$k}\" style=\"border:0px\" {$class} {$ext} {$checked}/> {$v}</label></span>\n ";
			if ($i % $cols == 0) {
				$data .= " \n";
			}
			$i++;
		}
		return $data;
	}
	public static function checkbox_key($options, $name, $id = '', $value = '', $cols = 5, $class = '', $ext = '', $width = 100)
	{
		if (!$options) {
			return '';
		}
		if (!$id) {
			$id = $name;
		}
		if (!is_array($options)) {
			$options = self::_option($options);
		}
		$i = 1;
		$data = "<input type=\"hidden\" name=\"" . $name . "\" value=\"-99\">";
		if ($class) {
			$class = " class=\"{$class}\"";
		}
		if ($value != '') {
			$value = strpos($value, ",") ? explode(",", $value) : array($value);
		}
		foreach ($options as $k => $v) {
			$checked = $value && in_array($k, $value) ? "checked" : '';
			$data .= "<span class=\"ckspan\"><label><input class=\"ck_style\" type=\"checkbox\" boxid=\"{$id}\" name=\"{$name}[]\" id=\"{$id}\" value=\"{$k}\" style=\"border:0px\" {$class} {$ext} {$checked}/> {$v}</label></span>\n ";
			if ($i % $cols == 0) {
				$data .= " \n";
			}
			$i++;
		}
		return $data;
	}
	public static function radio($options, $name, $id = '', $value = '', $cols = 5, $class = '', $ext = '', $width = 100)
	{
		if (!$id) {
			$id = $name;
		}
		if (!is_array($options)) {
			$options = self::_option($options);
		}
		$i = 1;
		$data = '';
		if ($class) {
			$class = " class=\"{$class}\"";
		}
		foreach ($options as $k => $v) {
			$checked = $k == $value ? "checked" : '';
			$data .= "<span class=\"radiospan\"><label><input class=\"radio_style\" type=\"radio\" name=\"{$name}\" id=\"{$id}\" value=\"{$k}\" style=\"border:0px\" {$class} {$ext} {$checked}/> {$v}</label></span> ";
			if ($i % $cols == 0) {
				$data .= " \n";
			}
			$i++;
		}
		return $data;
	}
	public static function rd_house_attr_value($attr, $check)
	{
		$rs = $dot = '';
		if (is_array($check)) {
			foreach ($check as $key => $val) {
				$rs .= $dot . $attr[$val];
				$dot = ",";
			}
		}
		return $rs;
	}
	public static function text($name, $id = '', $value = '', $type = "text", $size = 50, $class = '', $ext = '', $minlength = '', $maxlength = '', $pattern = '', $errortips = '')
	{
		if (!$id) {
			$id = $name;
		}
		$checkthis = '';
		$showerrortips = "字符长度必须为" . $minlength . "到" . $maxlength . "位";
		if ($pattern) {
			$pattern = "regexp=\"" . substr($pattern, 1, -1) . "\"";
		}
		$require = $minlength ? "true" : "false";
		if ($pattern && ($minlength || $maxlength)) {
			$string_datatype = substr($string_datatype, 1);
			$checkthis = "require=\"{$require}\" {$pattern} datatype=\"limit|custom\" min=\"{$minlength}\" max=\"{$maxlength}\" msg='{$showerrortips}|{$errortips}'";
		} else {
			if ($pattern) {
				$checkthis = "require=\"{$require}\" {$pattern} datatype=\"custom\" msg='{$errortips}'";
			} else {
				if ($minlength || $maxlength) {
					$checkthis = "require=\"{$require}\" datatype=\"limit\" min=\"{$minlength}\" max=\"{$maxlength}\" msg='{$showerrortips}'";
				}
			}
		}
		return "<input type=\"{$type}\" name=\"{$name}\" id=\"{$id}\" value=\"{$value}\" size=\"{$size}\" class=\"{$class}\" {$checkthis} {$ext}/> ";
	}
	public static function getTreeOption($data, $parentid = 0, $name = "catid", $id = '', $alt = '', $catid = 0, $property = '', $type = 0, $optgroup = 0)
	{
		$tree = new form_tree();
		$html = '';
		if (!$id) {
			$id = $name;
		}
		if ($optgroup) {
			$optgroup_str = "<optgroup label='\$name'></optgroup>";
		}
		$html .= "<select name='{$name}' id='{$id}' {$property}>\n<option value='0'>{$alt}</option>\n";
		if (is_array($data)) {
			$categorys = array();
			foreach ($data as $id => $cat) {
				$style = '';
				if ($cat["parent_id"] == 0) {
					$style = "style=\"background-color:#dadada; padding:2px; color:red; font-weight:bold;\"";
				}
				$categorys[$id] = array("id" => $cat["id"], "parentid" => $cat["parent_id"], "name" => $cat["category_name"], "style" => $style);
			}
			$tree->tree($categorys);
			$html .= $tree->get_tree($parentid, "<option value='\$id' \$style \$selected>\$spacer\$name</option>\n", $catid, '', $optgroup_str);
		}
		$html .= "</select>";
		return $html;
	}
	public static function get_category_table($data, $parentid = 0, $name = "catid", $id = '', $alt = '', $catid = 0, $property = '', $style = "align=\"left\"", $optgroup = 0)
	{
		$tree = new form_tree();
		$html = '';
		if (!$id) {
			$id = $name;
		}
		if ($optgroup) {
			$optgroup_str = "<optgroup label='\$name'></optgroup>";
		}
		$html .= " ";
		if (is_array($data)) {
			$categorys = array();
			foreach ($data as $id => $cat) {
				$style = '';
				if ($cat["parent_id"] == 0) {
					$style = "style=\"background-color:#dadada; padding:2px; color:red; font-weight:bold;\"";
				}
				$categorys[$id] = array("id" => $cat["id"], "parentid" => $cat["parent_id"], "name" => $cat["category_name"], "style" => $style, "goods_num" => $cat["goods_num"], "sort_order" => $cat["sort_order"], "panel" => "<td><a href=\"\">修改</a> | </td>");
			}
			$tree->tree($categorys);
			$html .= $tree->get_tree($parentid, "<tr \$property><td><input type=checkbox name=ids[] value='\$id'> \$id</td><td value='\$id' align=left \$style \$selected>\$spacer\$name</</td><td>\$goods_num</td><td>\$sort_order</td>\$panel</tr>\n", $catid, '', $optgroup_str);
		}
		$html .= " ";
		return $html;
	}
}