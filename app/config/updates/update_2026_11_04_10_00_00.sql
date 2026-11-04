START TRANSACTION;

UPDATE `options`
SET `value` = CONCAT('theme1|theme2|theme3|theme4|theme5|theme6|theme7|theme8|theme9|theme10|theme11::', SUBSTRING_INDEX(`value`, '::', -1)),
    `label` = 'Theme 1|Theme 2|Theme 3|Theme 4|Theme 5|Theme 6|Theme 7|Theme 8|Theme 9|Theme 10|Theme 11'
WHERE `key` = 'o_theme' AND `value` NOT LIKE '%theme11%';

-- 2) design options (foreign_id 1 = global script options; hidden - edited from the design panel)
INSERT IGNORE INTO `options` (`foreign_id`, `key`, `tab_id`, `value`, `label`, `type`, `order`, `is_visible`, `style`) VALUES
(1, 'o_theme11_page', NULL, '#fbf0ea', 'Theme 11 / page', 'string', NULL, 0, NULL),
(1, 'o_theme11_card', NULL, '#ffffff', 'Theme 11 / card', 'string', NULL, 0, NULL),
(1, 'o_theme11_soft', NULL, '#f4dccf', 'Theme 11 / soft', 'string', NULL, 0, NULL),
(1, 'o_theme11_line', NULL, '#f8e8e0', 'Theme 11 / line', 'string', NULL, 0, NULL),
(1, 'o_theme11_accent', NULL, '#e8836b', 'Theme 11 / accent', 'string', NULL, 0, NULL),
(1, 'o_theme11_heading', NULL, '#4a2a22', 'Theme 11 / heading', 'string', NULL, 0, NULL),
(1, 'o_theme11_body', NULL, '#674c45', 'Theme 11 / body', 'string', NULL, 0, NULL),
(1, 'o_theme11_muted', NULL, '#7d6660', 'Theme 11 / muted', 'string', NULL, 0, NULL),
(1, 'o_theme11_link', NULL, '#ae3c22', 'Theme 11 / link', 'string', NULL, 0, NULL),
(1, 'o_theme11_price', NULL, '#c94e33', 'Theme 11 / price', 'string', NULL, 0, NULL),
(1, 'o_theme11_btn', NULL, '#c94e33', 'Theme 11 / btn', 'string', NULL, 0, NULL),
(1, 'o_theme11_btn_text', NULL, '#ffffff', 'Theme 11 / btn text', 'string', NULL, 0, NULL),
(1, 'o_theme11_btn_hover', NULL, '#b73f24', 'Theme 11 / btn hover', 'string', NULL, 0, NULL),
(1, 'o_theme11_btn2', NULL, '#ffffff', 'Theme 11 / btn2', 'string', NULL, 0, NULL),
(1, 'o_theme11_btn2_text', NULL, '#ae3c22', 'Theme 11 / btn2 text', 'string', NULL, 0, NULL),
(1, 'o_theme11_slot_free', NULL, '#ae3c22', 'Theme 11 / slot free', 'string', NULL, 0, NULL),
(1, 'o_theme11_slot_sel', NULL, '#c94e33', 'Theme 11 / slot sel', 'string', NULL, 0, NULL),
(1, 'o_theme11_slot_sel_text', NULL, '#ffffff', 'Theme 11 / slot sel text', 'string', NULL, 0, NULL),
(1, 'o_theme11_slot_busy', NULL, '#efe1d9', 'Theme 11 / slot busy', 'string', NULL, 0, NULL),
(1, 'o_theme11_font_head', NULL, 'poppins', 'Theme 11 / font head', 'string', NULL, 0, NULL),
(1, 'o_theme11_font_body', NULL, 'inter', 'Theme 11 / font body', 'string', NULL, 0, NULL),
(1, 'o_theme11_font_size', NULL, '15', 'Theme 11 / font size', 'string', NULL, 0, NULL),
(1, 'o_theme11_radius', NULL, 'soft', 'Theme 11 / radius', 'string', NULL, 0, NULL),
(1, 'o_theme11_shadow', NULL, 'soft', 'Theme 11 / shadow', 'string', NULL, 0, NULL),
(1, 'o_theme11_steps', NULL, '1', 'Theme 11 / steps', 'string', NULL, 0, NULL);

-- 3) labels
INSERT IGNORE INTO `plugin_base_fields` (`id`, `key`, `type`, `label`, `source`, `modified`) VALUES
(NULL, 'option_themes_ARRAY_11', 'arrays', 'option_themes_ARRAY_11', 'script', NULL),
(NULL, 't11_customize', 'backend', 'Label / Customize design', 'script', NULL),
(NULL, 't11_panel_title', 'backend', 'Label / Theme 11 design', 'script', NULL),
(NULL, 't11_close', 'backend', 'Label / Close', 'script', NULL),
(NULL, 't11_panel_hint', 'backend', 'Label / Changes appear in the preview straight away. Press Save to p', 'script', NULL),
(NULL, 't11_palettes', 'backend', 'Label / Colour palettes', 'script', NULL),
(NULL, 't11_palette_coral', 'backend', 'Label / Sunset Coral', 'script', NULL),
(NULL, 't11_palette_sage', 'backend', 'Label / Sage', 'script', NULL),
(NULL, 't11_palette_ocean', 'backend', 'Label / Ocean', 'script', NULL),
(NULL, 't11_palette_lavender', 'backend', 'Label / Lavender', 'script', NULL),
(NULL, 't11_palette_forest', 'backend', 'Label / Forest', 'script', NULL),
(NULL, 't11_palette_gold', 'backend', 'Label / Gold', 'script', NULL),
(NULL, 't11_palette_rose', 'backend', 'Label / Rose', 'script', NULL),
(NULL, 't11_palette_mint', 'backend', 'Label / Mint', 'script', NULL),
(NULL, 't11_group_surfaces', 'backend', 'Label / Surfaces', 'script', NULL),
(NULL, 't11_group_text', 'backend', 'Label / Text', 'script', NULL),
(NULL, 't11_group_buttons', 'backend', 'Label / Buttons', 'script', NULL),
(NULL, 't11_group_slots', 'backend', 'Label / Time slots', 'script', NULL),
(NULL, 't11_c_page', 'backend', 'Label / Page background', 'script', NULL),
(NULL, 't11_c_card', 'backend', 'Label / Cards', 'script', NULL),
(NULL, 't11_c_soft', 'backend', 'Label / Soft areas / chips', 'script', NULL),
(NULL, 't11_c_line', 'backend', 'Label / Borders', 'script', NULL),
(NULL, 't11_c_accent', 'backend', 'Label / Accent', 'script', NULL),
(NULL, 't11_c_heading', 'backend', 'Label / Headings', 'script', NULL),
(NULL, 't11_c_body', 'backend', 'Label / Body text', 'script', NULL),
(NULL, 't11_c_muted', 'backend', 'Label / Muted text', 'script', NULL),
(NULL, 't11_c_link', 'backend', 'Label / Links', 'script', NULL),
(NULL, 't11_c_price', 'backend', 'Label / Prices', 'script', NULL),
(NULL, 't11_c_btn', 'backend', 'Label / Primary button', 'script', NULL),
(NULL, 't11_c_btn_text', 'backend', 'Label / Primary button text', 'script', NULL),
(NULL, 't11_c_btn_hover', 'backend', 'Label / Primary button hover', 'script', NULL),
(NULL, 't11_c_btn2', 'backend', 'Label / Secondary button', 'script', NULL),
(NULL, 't11_c_btn2_text', 'backend', 'Label / Secondary button text', 'script', NULL),
(NULL, 't11_c_slot_free', 'backend', 'Label / Available', 'script', NULL),
(NULL, 't11_c_slot_sel', 'backend', 'Label / Selected', 'script', NULL),
(NULL, 't11_c_slot_sel_text', 'backend', 'Label / Selected text', 'script', NULL),
(NULL, 't11_c_slot_busy', 'backend', 'Label / Booked', 'script', NULL),
(NULL, 't11_group_type', 'backend', 'Label / Typography', 'script', NULL),
(NULL, 't11_font_head', 'backend', 'Label / Heading font', 'script', NULL),
(NULL, 't11_font_body', 'backend', 'Label / Body font', 'script', NULL),
(NULL, 't11_font_size', 'backend', 'Label / Text size', 'script', NULL),
(NULL, 't11_font_note', 'backend', 'Label / Web fonts are loaded from Google Fonts. Choose "System UI" t', 'script', NULL),
(NULL, 't11_group_shape', 'backend', 'Label / Shape & layout', 'script', NULL),
(NULL, 't11_radius', 'backend', 'Label / Corners', 'script', NULL),
(NULL, 't11_radius_sharp', 'backend', 'Label / Sharp', 'script', NULL),
(NULL, 't11_radius_soft', 'backend', 'Label / Soft', 'script', NULL),
(NULL, 't11_radius_round', 'backend', 'Label / Round', 'script', NULL),
(NULL, 't11_shadow', 'backend', 'Label / Shadows', 'script', NULL),
(NULL, 't11_shadow_none', 'backend', 'Label / None', 'script', NULL),
(NULL, 't11_shadow_soft', 'backend', 'Label / Soft', 'script', NULL),
(NULL, 't11_shadow_lifted', 'backend', 'Label / Lifted', 'script', NULL),
(NULL, 't11_steps', 'backend', 'Label / Show the step tracker', 'script', NULL),
(NULL, 't11_reset', 'backend', 'Label / Reset to default', 'script', NULL),
(NULL, 't11_save', 'backend', 'Label / Save', 'script', NULL),
(NULL, 't11_msg_saved', 'backend', 'Label / Saved. Your website now uses these settings.', 'script', NULL),
(NULL, 't11_msg_reset', 'backend', 'Label / Default design restored.', 'script', NULL),
(NULL, 't11_msg_error', 'backend', 'Label / Could not save. Please try again.', 'script', NULL),
(NULL, 't11_msg_confirm', 'backend', 'Label / Restore the default design? Your saved colours and fonts wil', 'script', NULL),
(NULL, 'front_t11_step_service', 'frontend', 'Frontend / Service', 'script', NULL),
(NULL, 'front_t11_step_time', 'frontend', 'Frontend / Time', 'script', NULL),
(NULL, 'front_t11_step_details', 'frontend', 'Frontend / Details', 'script', NULL),
(NULL, 'front_t11_step_confirm', 'frontend', 'Frontend / Confirm', 'script', NULL),
(NULL, 'front_t11_legend_available', 'frontend', 'Frontend / Available', 'script', NULL),
(NULL, 'front_t11_legend_selected', 'frontend', 'Frontend / Selected', 'script', NULL),
(NULL, 'front_t11_legend_booked', 'frontend', 'Frontend / Booked', 'script', NULL);

-- German (de-AT) text for every German locale of the installation
INSERT IGNORE INTO `plugin_base_multi_lang` (`id`, `foreign_id`, `model`, `locale`, `field`, `content`, `source`)
SELECT NULL, f.`id`, 'pjBaseField', l.`locale`, 'title', t.`txt`, 'script'
FROM `plugin_base_fields` f
CROSS JOIN (SELECT `id` AS `locale` FROM `plugin_base_locale` WHERE `language_iso` LIKE 'de%') l
INNER JOIN (
SELECT 'option_themes_ARRAY_11' AS `k`, 'Theme 11' AS `txt`
UNION ALL SELECT 't11_customize', 'Design anpassen'
UNION ALL SELECT 't11_panel_title', 'Theme-11-Design'
UNION ALL SELECT 't11_close', 'Schließen'
UNION ALL SELECT 't11_panel_hint', 'Änderungen erscheinen sofort in der Vorschau. Klicken Sie auf Speichern, um sie auf Ihrer Website zu veröffentlichen.'
UNION ALL SELECT 't11_palettes', 'Farbpaletten'
UNION ALL SELECT 't11_palette_coral', 'Sunset Coral'
UNION ALL SELECT 't11_palette_sage', 'Salbei'
UNION ALL SELECT 't11_palette_ocean', 'Ozean'
UNION ALL SELECT 't11_palette_lavender', 'Lavendel'
UNION ALL SELECT 't11_palette_forest', 'Wald'
UNION ALL SELECT 't11_palette_gold', 'Gold'
UNION ALL SELECT 't11_palette_rose', 'Rosé'
UNION ALL SELECT 't11_palette_mint', 'Minze'
UNION ALL SELECT 't11_group_surfaces', 'Flächen'
UNION ALL SELECT 't11_group_text', 'Text'
UNION ALL SELECT 't11_group_buttons', 'Schaltflächen'
UNION ALL SELECT 't11_group_slots', 'Zeitfenster'
UNION ALL SELECT 't11_c_page', 'Seitenhintergrund'
UNION ALL SELECT 't11_c_card', 'Karten'
UNION ALL SELECT 't11_c_soft', 'Weiche Flächen / Chips'
UNION ALL SELECT 't11_c_line', 'Rahmen'
UNION ALL SELECT 't11_c_accent', 'Akzent'
UNION ALL SELECT 't11_c_heading', 'Überschriften'
UNION ALL SELECT 't11_c_body', 'Fließtext'
UNION ALL SELECT 't11_c_muted', 'Gedämpfter Text'
UNION ALL SELECT 't11_c_link', 'Links'
UNION ALL SELECT 't11_c_price', 'Preise'
UNION ALL SELECT 't11_c_btn', 'Primäre Schaltfläche'
UNION ALL SELECT 't11_c_btn_text', 'Text der primären Schaltfläche'
UNION ALL SELECT 't11_c_btn_hover', 'Primäre Schaltfläche (Hover)'
UNION ALL SELECT 't11_c_btn2', 'Sekundäre Schaltfläche'
UNION ALL SELECT 't11_c_btn2_text', 'Text der sekundären Schaltfläche'
UNION ALL SELECT 't11_c_slot_free', 'Verfügbar'
UNION ALL SELECT 't11_c_slot_sel', 'Ausgewählt'
UNION ALL SELECT 't11_c_slot_sel_text', 'Text ausgewählt'
UNION ALL SELECT 't11_c_slot_busy', 'Gebucht'
UNION ALL SELECT 't11_group_type', 'Typografie'
UNION ALL SELECT 't11_font_head', 'Überschriftenschrift'
UNION ALL SELECT 't11_font_body', 'Textschrift'
UNION ALL SELECT 't11_font_size', 'Textgröße'
UNION ALL SELECT 't11_font_note', 'Webschriften werden von Google Fonts geladen. Wählen Sie "System UI", um externe Anfragen zu vermeiden.'
UNION ALL SELECT 't11_group_shape', 'Form & Layout'
UNION ALL SELECT 't11_radius', 'Ecken'
UNION ALL SELECT 't11_radius_sharp', 'Eckig'
UNION ALL SELECT 't11_radius_soft', 'Weich'
UNION ALL SELECT 't11_radius_round', 'Rund'
UNION ALL SELECT 't11_shadow', 'Schatten'
UNION ALL SELECT 't11_shadow_none', 'Keine'
UNION ALL SELECT 't11_shadow_soft', 'Weich'
UNION ALL SELECT 't11_shadow_lifted', 'Hervorgehoben'
UNION ALL SELECT 't11_steps', 'Schrittanzeige einblenden'
UNION ALL SELECT 't11_reset', 'Auf Standard zurücksetzen'
UNION ALL SELECT 't11_save', 'Speichern'
UNION ALL SELECT 't11_msg_saved', 'Gespeichert. Ihre Website verwendet jetzt diese Einstellungen.'
UNION ALL SELECT 't11_msg_reset', 'Standarddesign wiederhergestellt.'
UNION ALL SELECT 't11_msg_error', 'Speichern nicht möglich. Bitte versuchen Sie es erneut.'
UNION ALL SELECT 't11_msg_confirm', 'Standarddesign wiederherstellen? Ihre gespeicherten Farben und Schriften werden ersetzt.'
UNION ALL SELECT 'front_t11_step_service', 'Leistung'
UNION ALL SELECT 'front_t11_step_time', 'Zeit'
UNION ALL SELECT 'front_t11_step_details', 'Angaben'
UNION ALL SELECT 'front_t11_step_confirm', 'Bestätigen'
UNION ALL SELECT 'front_t11_legend_available', 'Verfügbar'
UNION ALL SELECT 'front_t11_legend_selected', 'Ausgewählt'
UNION ALL SELECT 'front_t11_legend_booked', 'Gebucht'
) t ON t.`k` = f.`key`;

-- English text for every other locale (translate afterwards in the label editor)
INSERT IGNORE INTO `plugin_base_multi_lang` (`id`, `foreign_id`, `model`, `locale`, `field`, `content`, `source`)
SELECT NULL, f.`id`, 'pjBaseField', l.`locale`, 'title', t.`txt`, 'script'
FROM `plugin_base_fields` f
CROSS JOIN (SELECT DISTINCT `locale` FROM `plugin_base_multi_lang` WHERE `model` = 'pjBaseField' AND `locale` IS NOT NULL) l
INNER JOIN (
SELECT 'option_themes_ARRAY_11' AS `k`, 'Theme 11' AS `txt`
UNION ALL SELECT 't11_customize', 'Customize design'
UNION ALL SELECT 't11_panel_title', 'Theme 11 design'
UNION ALL SELECT 't11_close', 'Close'
UNION ALL SELECT 't11_panel_hint', 'Changes appear in the preview straight away. Press Save to publish them to your website.'
UNION ALL SELECT 't11_palettes', 'Colour palettes'
UNION ALL SELECT 't11_palette_coral', 'Sunset Coral'
UNION ALL SELECT 't11_palette_sage', 'Sage'
UNION ALL SELECT 't11_palette_ocean', 'Ocean'
UNION ALL SELECT 't11_palette_lavender', 'Lavender'
UNION ALL SELECT 't11_palette_forest', 'Forest'
UNION ALL SELECT 't11_palette_gold', 'Gold'
UNION ALL SELECT 't11_palette_rose', 'Rose'
UNION ALL SELECT 't11_palette_mint', 'Mint'
UNION ALL SELECT 't11_group_surfaces', 'Surfaces'
UNION ALL SELECT 't11_group_text', 'Text'
UNION ALL SELECT 't11_group_buttons', 'Buttons'
UNION ALL SELECT 't11_group_slots', 'Time slots'
UNION ALL SELECT 't11_c_page', 'Page background'
UNION ALL SELECT 't11_c_card', 'Cards'
UNION ALL SELECT 't11_c_soft', 'Soft areas / chips'
UNION ALL SELECT 't11_c_line', 'Borders'
UNION ALL SELECT 't11_c_accent', 'Accent'
UNION ALL SELECT 't11_c_heading', 'Headings'
UNION ALL SELECT 't11_c_body', 'Body text'
UNION ALL SELECT 't11_c_muted', 'Muted text'
UNION ALL SELECT 't11_c_link', 'Links'
UNION ALL SELECT 't11_c_price', 'Prices'
UNION ALL SELECT 't11_c_btn', 'Primary button'
UNION ALL SELECT 't11_c_btn_text', 'Primary button text'
UNION ALL SELECT 't11_c_btn_hover', 'Primary button hover'
UNION ALL SELECT 't11_c_btn2', 'Secondary button'
UNION ALL SELECT 't11_c_btn2_text', 'Secondary button text'
UNION ALL SELECT 't11_c_slot_free', 'Available'
UNION ALL SELECT 't11_c_slot_sel', 'Selected'
UNION ALL SELECT 't11_c_slot_sel_text', 'Selected text'
UNION ALL SELECT 't11_c_slot_busy', 'Booked'
UNION ALL SELECT 't11_group_type', 'Typography'
UNION ALL SELECT 't11_font_head', 'Heading font'
UNION ALL SELECT 't11_font_body', 'Body font'
UNION ALL SELECT 't11_font_size', 'Text size'
UNION ALL SELECT 't11_font_note', 'Web fonts are loaded from Google Fonts. Choose "System UI" to avoid any external request.'
UNION ALL SELECT 't11_group_shape', 'Shape & layout'
UNION ALL SELECT 't11_radius', 'Corners'
UNION ALL SELECT 't11_radius_sharp', 'Sharp'
UNION ALL SELECT 't11_radius_soft', 'Soft'
UNION ALL SELECT 't11_radius_round', 'Round'
UNION ALL SELECT 't11_shadow', 'Shadows'
UNION ALL SELECT 't11_shadow_none', 'None'
UNION ALL SELECT 't11_shadow_soft', 'Soft'
UNION ALL SELECT 't11_shadow_lifted', 'Lifted'
UNION ALL SELECT 't11_steps', 'Show the step tracker'
UNION ALL SELECT 't11_reset', 'Reset to default'
UNION ALL SELECT 't11_save', 'Save'
UNION ALL SELECT 't11_msg_saved', 'Saved. Your website now uses these settings.'
UNION ALL SELECT 't11_msg_reset', 'Default design restored.'
UNION ALL SELECT 't11_msg_error', 'Could not save. Please try again.'
UNION ALL SELECT 't11_msg_confirm', 'Restore the default design? Your saved colours and fonts will be replaced.'
UNION ALL SELECT 'front_t11_step_service', 'Service'
UNION ALL SELECT 'front_t11_step_time', 'Time'
UNION ALL SELECT 'front_t11_step_details', 'Details'
UNION ALL SELECT 'front_t11_step_confirm', 'Confirm'
UNION ALL SELECT 'front_t11_legend_available', 'Available'
UNION ALL SELECT 'front_t11_legend_selected', 'Selected'
UNION ALL SELECT 'front_t11_legend_booked', 'Booked'
) t ON t.`k` = f.`key`;

-- 4) label cache key
UPDATE `options` SET `value` = MD5(RAND()) WHERE `key` = 'o_fields_index';

COMMIT;
