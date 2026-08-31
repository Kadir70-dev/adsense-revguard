<?php
/**
 * Settings field type.
 *
 * @package QuickAdsense
 */

declare(strict_types=1);

namespace QuickAdsense\Settings;

/**
 * The kinds of settings field, used to drive sanitisation.
 */
enum FieldType {
	case Checkbox;
	case IntValue;
	case AdCode;
	case Text;
	case Multiselect;
}
