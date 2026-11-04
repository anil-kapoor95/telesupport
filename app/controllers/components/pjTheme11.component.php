<?php
if (!defined("ROOT_PATH"))
{
	header("HTTP/1.1 403 Forbidden");
	exit;
}
/**
 * Theme 11 - single source of truth for the customisable design tokens.
 *
 * Every token is stored as an option (o_theme11_<token>) and printed ONCE as a CSS custom property
 * (--t11-<token>) on the widget wrapper by pjFrontEnd::pjActionLoadCss(). theme11.css contains no colour,
 * font, radius or shadow literals - it only consumes those variables.
 */
class pjTheme11
{
	const THEME = 'theme11';
	
	/** colour tokens, in the order they are shown in the admin panel */
	public static function colorKeys()
	{
		return array('page','card','soft','line','accent','heading','body','muted','link','price','btn','btn_text','btn_hover','btn2','btn2_text','slot_free','slot_sel','slot_sel_text','slot_busy');
	}
	
	public static function fonts()
	{
		return array(
			'poppins' => array('name' => 'Poppins', 'stack' => "'Poppins', system-ui, sans-serif", 'google' => 'Poppins:wght@400;500;600;700'),
			'inter' => array('name' => 'Inter', 'stack' => "'Inter', system-ui, sans-serif", 'google' => 'Inter:wght@400;500;600;700'),
			'dm_sans' => array('name' => 'DM Sans', 'stack' => "'DM Sans', system-ui, sans-serif", 'google' => 'DM+Sans:wght@400;500;700'),
			'open_sans' => array('name' => 'Open Sans', 'stack' => "'Open Sans', system-ui, sans-serif", 'google' => 'Open+Sans:wght@400;600;700'),
			'playfair' => array('name' => 'Playfair Display', 'stack' => "'Playfair Display', Georgia, serif", 'google' => 'Playfair+Display:wght@600;700'),
			'georgia' => array('name' => 'Georgia', 'stack' => "Georgia, 'Times New Roman', serif", 'google' => NULL),
			'system' => array('name' => 'System UI (no external font)', 'stack' => "system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif", 'google' => NULL)
		);
	}
	
	/** corner presets: card radius / button radius / input radius */
	public static function radii()
	{
		return array(
			'sharp' => array('r' => '6px', 'rb' => '6px', 'ri' => '6px'),
			'soft' => array('r' => '16px', 'rb' => '12px', 'ri' => '10px'),
			'round' => array('r' => '24px', 'rb' => '999px', 'ri' => '14px')
		);
	}
	
	public static function shadows()
	{
		return array(
			'none' => 'none',
			'soft' => '0 10px 30px rgba(60, 40, 30, 0.08)',
			'lifted' => '0 18px 44px rgba(60, 40, 30, 0.18)'
		);
	}
	
	public static function palettes()
	{
		return array(
		'coral' => array('name' => 'Sunset Coral', 'colors' => array(
			'page' => '#fbf0ea',
			'card' => '#ffffff',
			'soft' => '#f4dccf',
			'line' => '#f8e8e0',
			'accent' => '#e8836b',
			'heading' => '#4a2a22',
			'body' => '#674c45',
			'muted' => '#7d6660',
			'link' => '#ae3c22',
			'price' => '#c94e33',
			'btn' => '#c94e33',
			'btn_text' => '#ffffff',
			'btn_hover' => '#b73f24',
			'btn2' => '#ffffff',
			'btn2_text' => '#ae3c22',
			'slot_free' => '#ae3c22',
			'slot_sel' => '#c94e33',
			'slot_sel_text' => '#ffffff',
			'slot_busy' => '#efe1d9'
		)),
		'sage' => array('name' => 'Sage', 'colors' => array(
			'page' => '#f1f4ec',
			'card' => '#ffffff',
			'soft' => '#dde6d0',
			'line' => '#e9efe0',
			'accent' => '#8fa876',
			'heading' => '#2f3a26',
			'body' => '#505a49',
			'muted' => '#697163',
			'link' => '#4d6837',
			'price' => '#5f7d45',
			'btn' => '#5f7d45',
			'btn_text' => '#ffffff',
			'btn_hover' => '#4d6837',
			'btn2' => '#ffffff',
			'btn2_text' => '#4d6837',
			'slot_free' => '#4d6837',
			'slot_sel' => '#5f7d45',
			'slot_sel_text' => '#ffffff',
			'slot_busy' => '#e1e7da'
		)),
		'ocean' => array('name' => 'Ocean', 'colors' => array(
			'page' => '#edf4fa',
			'card' => '#ffffff',
			'soft' => '#d3e5f4',
			'line' => '#e2eef8',
			'accent' => '#4f93c9',
			'heading' => '#1b3550',
			'body' => '#3f556c',
			'muted' => '#5b6e81',
			'link' => '#245f93',
			'price' => '#2a6fa8',
			'btn' => '#2a6fa8',
			'btn_text' => '#ffffff',
			'btn_hover' => '#1f5a8c',
			'btn2' => '#ffffff',
			'btn2_text' => '#245f93',
			'slot_free' => '#245f93',
			'slot_sel' => '#2a6fa8',
			'slot_sel_text' => '#ffffff',
			'slot_busy' => '#dbe6ef'
		)),
		'lavender' => array('name' => 'Lavender', 'colors' => array(
			'page' => '#f4f0fa',
			'card' => '#ffffff',
			'soft' => '#e3d9f2',
			'line' => '#ede6f7',
			'accent' => '#9a7fd0',
			'heading' => '#33284f',
			'body' => '#544a6b',
			'muted' => '#6c6480',
			'link' => '#5b4396',
			'price' => '#6b4fb0',
			'btn' => '#6b4fb0',
			'btn_text' => '#ffffff',
			'btn_hover' => '#59409a',
			'btn2' => '#ffffff',
			'btn2_text' => '#5b4396',
			'slot_free' => '#5b4396',
			'slot_sel' => '#6b4fb0',
			'slot_sel_text' => '#ffffff',
			'slot_busy' => '#e5dfee'
		)),
		'forest' => array('name' => 'Forest', 'colors' => array(
			'page' => '#eef3ef',
			'card' => '#ffffff',
			'soft' => '#d2e2d7',
			'line' => '#e2ece5',
			'accent' => '#3f8a5f',
			'heading' => '#16301f',
			'body' => '#3b5143',
			'muted' => '#576a5e',
			'link' => '#1a5c3b',
			'price' => '#1f6b44',
			'btn' => '#1f6b44',
			'btn_text' => '#ffffff',
			'btn_hover' => '#17573a',
			'btn2' => '#ffffff',
			'btn2_text' => '#1a5c3b',
			'slot_free' => '#1a5c3b',
			'slot_sel' => '#1f6b44',
			'slot_sel_text' => '#ffffff',
			'slot_busy' => '#dbe4de'
		)),
		'gold' => array('name' => 'Gold', 'colors' => array(
			'page' => '#fbf6e9',
			'card' => '#ffffff',
			'soft' => '#f2e6bf',
			'line' => '#f7efd5',
			'accent' => '#d6a52a',
			'heading' => '#3d3112',
			'body' => '#5c5238',
			'muted' => '#736b54',
			'link' => '#855f05',
			'price' => '#946a08',
			'btn' => '#946a08',
			'btn_text' => '#ffffff',
			'btn_hover' => '#7d5805',
			'btn2' => '#ffffff',
			'btn2_text' => '#855f05',
			'slot_free' => '#855f05',
			'slot_sel' => '#946a08',
			'slot_sel_text' => '#ffffff',
			'slot_busy' => '#eee7cf'
		)),
		'rose' => array('name' => 'Rose', 'colors' => array(
			'page' => '#fbeff3',
			'card' => '#ffffff',
			'soft' => '#f6d6e0',
			'line' => '#f9e4eb',
			'accent' => '#e0708f',
			'heading' => '#4a2130',
			'body' => '#674551',
			'muted' => '#7d5f6a',
			'link' => '#a52e56',
			'price' => '#c23b66',
			'btn' => '#c23b66',
			'btn_text' => '#ffffff',
			'btn_hover' => '#a82f56',
			'btn2' => '#ffffff',
			'btn2_text' => '#a52e56',
			'slot_free' => '#a52e56',
			'slot_sel' => '#c23b66',
			'slot_sel_text' => '#ffffff',
			'slot_busy' => '#f0dde3'
		)),
		'mint' => array('name' => 'Mint', 'colors' => array(
			'page' => '#ebf7f4',
			'card' => '#ffffff',
			'soft' => '#cdeee6',
			'line' => '#def4ef',
			'accent' => '#3fb8a0',
			'heading' => '#103a33',
			'body' => '#365a54',
			'muted' => '#53716c',
			'link' => '#0f6a5a',
			'price' => '#13806d',
			'btn' => '#13806d',
			'btn_text' => '#ffffff',
			'btn_hover' => '#0e6a5a',
			'btn2' => '#ffffff',
			'btn2_text' => '#0f6a5a',
			'slot_free' => '#0f6a5a',
			'slot_sel' => '#13806d',
			'slot_sel_text' => '#ffffff',
			'slot_busy' => '#d8ece7'
		))
		);
	}
	
	/** non-colour defaults */
	public static function otherDefaults()
	{
		return array('font_head' => 'poppins', 'font_body' => 'inter', 'font_size' => '15', 'radius' => 'soft', 'shadow' => 'soft', 'steps' => '1');
	}
	
	public static function defaults()
	{
		$palettes = self::palettes();
		return array_merge($palettes['coral']['colors'], self::otherDefaults());
	}
	
	/** every option key the theme owns (o_theme11_*) */
	public static function optionKeys()
	{
		return array_keys(self::defaults());
	}
	
	/**
	 * Validate one value for one token. Returns the clean value, or NULL when it is not acceptable.
	 */
	public static function clean($token, $value)
	{
		if (!is_scalar($value))
		{
			return NULL;
		}
		$value = trim((string) $value);
		if (in_array($token, self::colorKeys(), true))
		{
			return preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? strtolower($value) : NULL;
		}
		switch ($token)
		{
			case 'font_head':
			case 'font_body':
				$fonts = self::fonts();
				return isset($fonts[$value]) ? $value : NULL;
			case 'font_size':
				return (preg_match('/^\d{2}$/', $value) && (int) $value >= 13 && (int) $value <= 17) ? (string) (int) $value : NULL;
			case 'radius':
				$r = self::radii();
				return isset($r[$value]) ? $value : NULL;
			case 'shadow':
				$s = self::shadows();
				return isset($s[$value]) ? $value : NULL;
			case 'steps':
				return ($value === '0' || $value === '1') ? $value : NULL;
		}
		return NULL;
	}
	
	/** saved option values merged over the defaults; invalid saved values fall back to the default */
	public static function values($option_arr)
	{
		$values = self::defaults();
		foreach ($values as $token => $default)
		{
			$key = 'o_theme11_' . $token;
			if (isset($option_arr[$key]))
			{
				$clean = self::clean($token, $option_arr[$key]);
				if ($clean !== NULL)
				{
					$values[$token] = $clean;
				}
			}
		}
		return $values;
	}
	
	/** theme name coming from ?theme= : only theme1 ... theme11 (or 1 ... 11) are accepted, otherwise NULL */
	public static function sanitizeThemeName($theme)
	{
		if (!is_string($theme) || !preg_match('/^(?:theme)?(\d{1,2})$/', $theme, $m))
		{
			return NULL;
		}
		$n = (int) $m[1];
		return ($n >= 1 && $n <= 11) ? 'theme' . $n : NULL;
	}
	
	/** @import line for the web fonts that are really used (empty when only system fonts are selected) */
	public static function fontImport($values)
	{
		$fonts = self::fonts();
		$families = array();
		foreach (array($values['font_head'], $values['font_body']) as $f)
		{
			if (!empty($fonts[$f]['google']))
			{
				$families[$fonts[$f]['google']] = 1;
			}
		}
		if (empty($families))
		{
			return '';
		}
		return '@import url("https://fonts.googleapis.com/css2?family=' . implode('&family=', array_keys($families)) . '&display=swap");' . "\n";
	}
	
	/**
	 * "Massage, Training" -> one <span> per service (Theme 11 draws them as chips). The comma stays in its own span,
	 * so the other themes look exactly as before (they print the same text) and Theme 11 simply hides it.
	 */
	public static function positionChips($services)
	{
		$parts = array_filter(array_map('trim', explode(',', (string) $services)), 'strlen');
		$html = array();
		foreach ($parts as $part)
		{
			$html[] = '<span class="pjAsT11Chip">' . pjSanitize::html($part) . '</span>';
		}
		return implode('<span class="pjAsT11Sep">, </span>', $html);
	}
	
	/** the --t11-* block for the given wrapper element id, e.g. pjWrapperAppScheduler_1 */
	public static function cssVariables($values, $wrapper_id)
	{
		$fonts = self::fonts();
		$radii = self::radii();
		$shadows = self::shadows();
		$vars = array();
		foreach (self::colorKeys() as $token)
		{
			$vars['--t11-' . str_replace('_', '-', $token)] = $values[$token];
		}
		$vars['--t11-font-head'] = $fonts[$values['font_head']]['stack'];
		$vars['--t11-font-body'] = $fonts[$values['font_body']]['stack'];
		$vars['--t11-fs'] = $values['font_size'] . 'px';
		$vars['--t11-r'] = $radii[$values['radius']]['r'];
		$vars['--t11-rb'] = $radii[$values['radius']]['rb'];
		$vars['--t11-ri'] = $radii[$values['radius']]['ri'];
		$vars['--t11-shadow'] = $shadows[$values['shadow']];
		$vars['--t11-steps'] = $values['steps'] === '1' ? 'flex' : 'none';
		
		$css = '#' . $wrapper_id . ' {';
		foreach ($vars as $name => $value)
		{
			$css .= ' ' . $name . ': ' . $value . ';';
		}
		return $css . " }\n";
	}
	
	/** data handed to the admin panel (palettes, fonts, shapes, current values) */
	public static function adminData($option_arr)
	{
		$fonts = array();
		foreach (self::fonts() as $k => $f)
		{
			$fonts[$k] = array('name' => $f['name'], 'stack' => $f['stack'], 'google' => $f['google']);
		}
		$palettes = array();
		foreach (self::palettes() as $k => $p)
		{
			$palettes[$k] = $p;
		}
		return array(
			'values' => self::values($option_arr),
			'defaults' => self::defaults(),
			'palettes' => $palettes,
			'fonts' => $fonts,
			'radii' => self::radii(),
			'shadows' => self::shadows(),
			'colors' => self::colorKeys()
		);
	}
}
?>