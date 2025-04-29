<?php
/*
	Redaxo-Addon: Medienpool-Kategorien beschränken
	Boot (weitere Konfigurationen & Einbindung)
	v0.1
	by Falko Müller @ 2025
*/

/** RexStan: Vars vom Check ausschließen */
/** @var rex_addon $this */


//Beschränkungen durchführen, wenn nicht Admin bzw. nicht alle MP-Kategorien
if (rex::isBackend() && !mediapool_permissions::hasAllMediaPerm()):

	//Media-Liste+Details ausblenden, wenn nicht berechtigt
	if (rex_be_controller::getCurrentPagePart(1) == 'mediapool' && rex_be_controller::getCurrentPagePart(2) == 'media'):
	
		if (rex_request('file_id', 'int') > 0):
			//Detailseite ausblenden
			rex_extension::register('OUTPUT_FILTER', function($ep){
				
				$op 	= $ep->getSubject();
				$fileId	= rex_request('file_id', 'int');
			
				if (!mediapool_permissions::hasMediaPerm($fileId)):
					$op = preg_replace("/<section class\s*=\s*[\"']{1}rex-page-section[\"']{1}[^\>]*>.*<\/section>/imsU", '<section class="rex-page-section">'.rex_i18n::msg('a1910_accessdenied').'</section>', $op);
				endif;
				
				return $op;
			});
		
		else:
			//Medialiste Startkategorie ändern
			$sCatId = rex_session('media[rex_file_category]', 'int');			
			//dump($sCatId);
			
			$perm 	= rex::requireUser()->getComplexPerm('media');
			
				if (!$perm->hasCategoryPerm($sCatId)):
					$refl = new ReflectionClass($perm);
						$prop = $refl->getProperty('perms');
						$prop->setAccessible(true);
					$catIds = $prop->getValue($perm);
					
					$catId = (isset($catIds[0])) ? $catIds[0] : $sCatId;
				
					rex_set_session('media[rex_file_category]', $catId);
				endif;
						
		
			//Medialiste ausblenden, wenn nicht berechtigt
			rex_extension::register('OUTPUT_FILTER', function($ep){
				
				$op 	= $ep->getSubject();
				$catId = rex_request('rex_file_category', 'int', -1);
					$catId = ($catId == -1) ? rex_session('media[rex_file_category]', 'int') : $catId;
													
				if (!mediapool_permissions::hasMediaCatPerm($catId)):
					$op = preg_replace("/<div class\s*=\s*[\"']{1}panel-title[\"']{1}[^\>]*>.*<\/div>/imsU", '', $op);
					$op = preg_replace("/<table class\s*=\s*[\"']{1}table table-striped[^\>]*>.*<\/table>/imsU", '<table class="table table-striped table-hover"><tr><td>'.rex_i18n::msg('a1910_accessdenied').'</td></tr></table>', $op);
				endif;				
				
				return $op;
			});
		
		endif;
				
	endif;	
	
	
	
	//Media-Detail-Sidebar zusätzlich ausblenden
	rex_extension::register('MEDIA_DETAIL_SIDEBAR', function($ep){
		
		$op 		= $ep->getSubject();
		$id			= $ep->getParam('id');
		
		if (!mediapool_permissions::hasMediaPerm($id)):
			$op = '';
		endif;
				
		return $op;
		
	});



	//Media-Kategorieauswahl beschränken (Original: redaxo/src/addons/mediapool/pages/media.php)
	rex_extension::register('MEDIA_LIST_TOOLBAR', function($ep){ 
	
		$op 				= $ep->getSubject();
		$rexFileCategory	= intval($ep->getParam('category_id'));
		
		
		$rexFileCategory	= (isset($rexFileCategory) && is_int($rexFileCategory)) ? $rexFileCategory : 0;
		$argFields			= (isset($argFields) && is_string($argFields)) ? $argFields : '';
		$fileId 			= (isset($fileId) && is_int($fileId)) ? $fileId : 0;

		$mediaName = rex_request('media_name', 'string');

		// *************************************** KATEGORIEN CHECK UND AUSWAHL

		$selMedia = new rex_media_category_select($checkPerm = true);
		$selMedia->setId('rex_file_category');
		$selMedia->setName('rex_file_category');
		$selMedia->setSize(1);
		$selMedia->setSelected($rexFileCategory);
		$selMedia->setAttribute('onchange', 'this.form.submit();');
		$selMedia->setAttribute('class', 'selectpicker');
		$selMedia->setAttribute('data-live-search', 'true');

		if (rex::requireUser()->getComplexPerm('media')->hasAll()) {
			$selMedia->addOption(rex_i18n::msg('pool_kats_no'), '0');
		}


		$formElements = [];
		$n = [];
		$n['field'] = '<input class="form-control" style="border-left: 0;" type="text" name="media_name" id="be_search-media-name" value="' . rex_escape($mediaName) . '" />';
		$n['right'] = '<button class="btn btn-search" type="submit"><i class="rex-icon rex-icon-search"></i></button>';
		$formElements[] = $n;
		$fragment = new rex_fragment();
		$fragment->setVar('elements', $formElements, false);

		$formElements = [];
		$n = [];
		$n['before'] = $selMedia->get();
		$n['after'] = '<search role="search">' . $fragment->parse('core/form/input_group.php') . '</search>';
		$formElements[] = $n;

		$fragment = new rex_fragment();
		$fragment->setVar('elements', $formElements, false);
		$toolbar = '<div class="rex-truncate-dropdown">' . $fragment->parse('core/form/input_group.php') . '</div>';

		$toolbar = '
		<div class="navbar-form navbar-right">
		<form action="' . rex_url::currentBackendPage() . '" method="post">
			' . $argFields . '
			<div class="form-group">
			' . $toolbar . '
			</div>
		</form>
		</div>';		
		
		
		return $toolbar;

	}, rex_extension::LATE);

endif;
?>