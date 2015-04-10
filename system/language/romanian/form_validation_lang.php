<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2015, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

$lang['form_validation_required']		= 'Campul {field} este obligatoriu.';
$lang['form_validation_isset']			= 'Campul {field} trebuie sa aiba o valoare.';
$lang['form_validation_valid_email']		= 'Campul {field} trebuie sa contina o adresa de email valida.';
$lang['form_validation_valid_emails']		= 'Campul {field} trebuie sa contina numai adrese de email valide.';
$lang['form_validation_valid_url']		= 'Campul {field} trebuie sa contina un link valid.';
$lang['form_validation_valid_ip']		= 'Campul {field} trebuie sa contina un IP valid.';
$lang['form_validation_min_length']		= 'Campul {field} trebuie sa aiba cel putin {param} caractere.';
$lang['form_validation_max_length']		= 'Campul {field} nu poate depasi {param} caractere.';
$lang['form_validation_exact_length']		= 'Campul {field} trebuie sa aiba exact {param} caractere.';
$lang['form_validation_alpha']			= 'Campul {field} poate contine doar litere.';
$lang['form_validation_alpha_numeric']		= 'Campul {field} poate contine doar litere si cifre.';
$lang['form_validation_alpha_numeric_spaces']	= 'Campul {field} poate contine doar litere, cifre si spatii.';
$lang['form_validation_alpha_dash']		= 'Campul {field} poate contine doar litere, cifre, underscore si linii.';
$lang['form_validation_numeric']		= 'Campul {field} poate contine doar numere.';
$lang['form_validation_is_numeric']		= 'Campul {field} poate contine doar valori numerice.';
$lang['form_validation_integer']		= 'Campul {field} trebuie sa contina un numar intreg.';
$lang['form_validation_regex_match']		= 'Campul {field} nu are formatul corect.';
// Tradus doar pana aici
$lang['form_validation_matches']		= 'The {field} field does not match the {param} field.';
$lang['form_validation_differs']		= 'The {field} field must differ from the {param} field.';
$lang['form_validation_is_unique'] 		= 'The {field} field must contain a unique value.';
$lang['form_validation_is_natural']		= 'The {field} field must only contain digits.';
$lang['form_validation_is_natural_no_zero']	= 'The {field} field must only contain digits and must be greater than zero.';
$lang['form_validation_decimal']		= 'The {field} field must contain a decimal number.';
$lang['form_validation_less_than']		= 'The {field} field must contain a number less than {param}.';
$lang['form_validation_less_than_equal_to']	= 'The {field} field must contain a number less than or equal to {param}.';
$lang['form_validation_greater_than']		= 'The {field} field must contain a number greater than {param}.';
$lang['form_validation_greater_than_equal_to']	= 'The {field} field must contain a number greater than or equal to {param}.';
$lang['form_validation_error_message_not_set']	= 'Unable to access an error message corresponding to your field name {field}.';
$lang['form_validation_in_list']		= 'The {field} field must be one of: {param}.';
