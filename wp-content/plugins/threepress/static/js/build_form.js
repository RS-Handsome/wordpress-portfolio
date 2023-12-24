import * as lib from './lib.js?v=162'







const build_section = ( name ) => {
	const section = lib.b('div', false, 'gallery-section')
	const header = lib.b('h3')
	header.innerHTML = name
	if( name !== 'section-toggle' ){
		header.classList.add('section-toggle')
		let hidden = false
		header.addEventListener('click', () => {
			section.classList.toggle('threepress-section-hidden')
		})
	}

	section.append( header )
	return section
}




const build_category = ( name, addClass ) => {
	const category = lib.b('div', false, 'threepress-options-category')
	if( addClass ){
		category.classList.add( addClass )
	}
	const header = lib.b('h4')
	header.innerHTML = name
	category.append( header )
	return category
}






export default ( gallery, output_container ) => {

	//////////////////////////////////////////////// build

	const form = lib.b('div', 'gallery-form')
	// form.action = 'create-gallery.php'
	form.method = 'post'

	gallery.form = form

	// preview toggle
	const preview = lib.b('div', 'gallery-preview', 'threepress-button', 'form-action')
	preview.title = 'gallery preview'
	const preview_img = lib.b('img')
	preview_img.src = THREEPRESS.plugin_url + '/assets/eye-viz.png'
	preview.append( preview_img )
	form.append( preview )

	// gallery name
	const name = build_section('gallery name')
	const input_name = lib.b('input')
	input_name.name = 'gallery_name'
	input_name.type = 'text'
	input_name.placeholder = 'gallery name (not displayed publicly)'
	name.append( input_name )
	const clar_name = lib.b('div', false, 'clarification')
	clar_name.innerHTML = 'This will also append an <code>id</code> to the gallery element of form <code>#threepress-gallery-[name]</code>'
	name.append( clar_name )
	form.append( name )

	// gallery model choose
	const model = build_section('model (required)')
	const p_model = lib.b('p')
	const model_choice = lib.b('div', 'model-choice')
	p_model.append( model_choice )
	model.append( p_model )
	const choose = lib.b('div', 'choose-model','button', 'button-primary')
	choose.innerHTML = 'choose model'
	choose.addEventListener('click', () => {
		console.log('shim to test for decoding')
		gallery.use_mesh_decode = gallery.form.querySelector('input[name=use_mesh_decode]')?.checked
	})
	const clips = lib.b('div', 'anim-clips')
	const clip_label = lib.b('div')
	clip_label.innerHTML = '<h4>found animation clips:</h4>'
	model.append( choose )
	model.append( clip_label )
	model.append( clips )
	form.append( model )


	// gallery ground (texture)
	const ground = build_section('ground (optional)')
	const g_img = lib.b('p')
	const ground_choice = lib.b('div', 'ground-choice')
	g_img.append( ground_choice )
	ground.append( g_img )
	const choose_img = lib.b('div', 'choose-ground', 'button', 'button-primary')
	choose_img.innerHTML = 'choose ground image'
	ground.append( choose_img )
	const clear_ground = lib.b('div', false, 'button', 'button-primary', 'clear')
	clear_ground.innerHTML = '&times;'
	ground.append( clear_ground )
	clear_ground.addEventListener('click', () => {
		const selected = ground_choice.querySelector('.threepress-row')
		if( selected ) selected.remove()
		const tex_selected = ground_choice_map.querySelector('.threepress-row')
		if( tex_selected ) tex_selected.remove()
		gallery.render_shortcode()
	})
	ground.append( clear_ground )
	form.append( ground )

	const model_float = build_category( 'model float', 'model-float')
	const float_height = lib.build_option('number', 'float_height', 0, 'model distance from ground', false, true, { 
		min: -1000, max: 1000,
	})
	model_float.append( float_height )
	const float_tip = lib.b('div', false, 'tip', 'active')
	float_tip.innerHTML = 'tip: float does not take into account heightmaps - adjust accordingly'
	model_float.append( float_tip )
	ground.append( model_float )

	// gallery ground size
	const ground_dimensions = build_category('ground dimensions', 'ground-dimensions')
	const ground_dims = lib.build_positioner('ground-dimensions', gallery, 'ground' )
	ground_dimensions.append( ground_dims )
	ground.append( ground_dimensions )

	const ground_res = build_category( 'ground resolution', 'ground-resolution')
	const res_input = lib.build_option('range', 'ground_resolution', 1, 'ground heightmap resolution', false, true, { 
		min: 0, max: 2,
	})
	ground_res.append( res_input )
	ground.append( ground_res )

	// gallery ground map
	// const ground_map = build_section('ground image map (optional)')
	const g_img_map = lib.b('p')
	const ground_choice_map = lib.b('div', 'ground-choice-map')
	g_img_map.append( ground_choice_map )
	ground.append( g_img_map )

	const choose_img_map = lib.b('div', 'choose-ground-map', 'button', 'button-primary')
	choose_img_map.innerHTML = 'choose heightmap'
	ground.append( choose_img_map )

	const clear_choice = lib.b('div', false, 'button', 'button-primary', 'clear')
	clear_choice.innerHTML = '&times;'
	ground.append( clear_choice )
	clear_choice.addEventListener('click', () => {
		const selected = ground_choice_map.querySelector('.threepress-row')
		if( selected ) selected.remove()
		gallery.render_shortcode()
		gallery.set_tooltips()
	})

	const map_explain = lib.b('div')
	map_explain.innerHTML = 'This image will be used as a <a href="https://en.wikipedia.org/wiki/Heightmap" target="_blank">heightmap</a> for the ground'
	ground.append( map_explain )
	form.append( ground )

	// option - controls
	const control_section = build_section('controls')
	const controls = build_category('controls')
	// options_content.append( controls )
	const controls_options = ['none', 'orbit'] // , 'first', 'flight'
	const disabled_opt = ['first', 'flight']
	let opt
	for( let i = 0; i < controls_options.length; i++ ){
		opt = lib.build_option( 'radio', 'options_controls', controls_options[i], controls_options[i], false, false, {}, i === 0 )
		if( disabled_opt.includes( controls_options[i] ) ) opt.classList.add('threepress-disabled')
		controls.append( opt )
	}
	control_section.append( controls )
	form.append( control_section )

	// option - camera
	const camera_section = build_section('camera')
	const camera = build_category('camera')
	const zoom = lib.build_option('checkbox', 'allow_zoom', false, 'allow zoom', false, false )
	const zoom_speed = lib.build_option('range', 'zoom_speed', false, false, false, true, { min: 1, max: 50, })
	const initial_zoom = lib.build_option('range', 'initial_zoom', 5, 'initial zoom', false, false, { min: 1, max: 10 } )
	camera.append( zoom )
	camera.append( zoom_speed )
	camera.append( initial_zoom )
	camera.append( lib.b('br') )
	// option - rotation
	const rotation = build_category('rotation')
	const auto = lib.build_option('checkbox', 'rotate_scene', false, 'auto rotate', false, false)
	rotation.append( auto )
	const rotate_speed = lib.build_option('range', 'rotate_speed', 20, 'rotation speed', false, true, { min: 1, max: 50 })
	rotation.append( rotate_speed )
	// options_content.append( rotation )
	camera_section.append( camera )
	camera_section.append( rotation )
	// options_content.append( camera )

	// option - no controls cam setting
	const cam_pos = build_category('camera angle', 'cam-position')
	const cam_setting = lib.build_positioner('cam-position', gallery )
	cam_pos.append( cam_setting )
	camera_section.append( cam_pos )
	form.append( camera_section )
	// options_content.append( cam_pos )

	// option - light
	const light_section = build_section('light')
	const light = build_category('light')
	const light_options = ['directional', 'sun', 'hemispherical']
	const disabled_light = ['hemispherical']
	for( let i = 0; i < light_options.length; i++ ){
		opt = lib.build_option('radio', 'options_light', light_options[i], light_options[i], false, false, {}, i === 0 )
		if( disabled_light.includes( light_options[i] ) ) opt.classList.add('threepress-disabled')
		light.append( opt )
	}
	const lensflare = lib.build_option('checkbox', 'has_lensflare', true, 'lensflare', false, true )
	const intensity = lib.build_option('range', 'intensity', 5, false, false, false, {
		min: 0,
		max: 15,
	})

	light.append( intensity )
	light.append( lensflare )
	light_section.append( light )
	// options_content.append( light )

	// option - no controls light setting
	const light_pos = build_category('light angle', 'light-position')
	const light_setting = lib.build_positioner('light-position', gallery )
	light_pos.append( light_setting )
	// options_content.append( light_pos )
	light_section.append( light_pos )


	const amb_section = build_category('ambience', 'ambience')
	const ambience = lib.build_option('range', 'ambience', 1, 'ambient light', false, true, {
		min: 0,
		max: 10,
	})
	amb_section.append( ambience )
	const amb_color = lib.build_option('color', 'ambient_color', false, ' ')
	amb_section.append( amb_color )
	light.append( amb_section )

	// hdr
	const hdr = build_category('hdr')

	const hdr_courtyard = lib.build_option('checkbox', 'hdr_courtyard', false, 'courtyard', false, false )
	hdr.append( hdr_courtyard )
	const hdr_galaxy = lib.build_option('checkbox', 'hdr_galaxy', false, 'galaxy', false, false )
	hdr.append( hdr_galaxy )
	const hdr_bridge = lib.build_option('checkbox', 'hdr_bridge', false, 'bridge', false, false )
	hdr.append( hdr_bridge )
	const hdr_park = lib.build_option('checkbox', 'hdr_park', false, 'park', false, false )
	hdr.append( hdr_park )
	const hdr_castle = lib.build_option('checkbox', 'hdr_castle', false, 'castle', false, false )
	hdr.append( hdr_castle )

	hdr.append( lib.b('br') )
	const show_hdr = lib.build_option('checkbox', 'show_hdr', false, 'show HDR image as background', false, false )
	hdr.append( show_hdr )
	light_section.append( hdr )

	// bloom
	const bloom = build_category('bloom')
	const bloom_on = lib.build_option('checkbox', 'has_bloom', false, 'bloom effect', false, false )
	bloom.append( bloom_on )
	const threshold = lib.build_option('range', 'bloom_threshold', 5, 'threshold', false, true, {
		min: 0,
		max: 10,
	})
	bloom.append( threshold )
	const strength = lib.build_option('range', 'bloom_strength', 5, 'strength', false, true, {
		min: 0,
		max: 10,
	})
	bloom.append( strength )
	const bloom_expl = lib.b('div')
	bloom_expl.classList.add('tip', 'active')
	bloom_expl.innerHTML = 'tip: currently, the bloom shader does not play well with heightmaps; the heightmap will appear to be semi-transparent'
	bloom.append( bloom_expl )
	light_section.append( bloom )
	// options_content.append( bloom )

	form.append( light_section)

	// environment
	const env_section = build_section('environment')

	// option - css bg
	const bg = build_category('css background')
	const color_picker = lib.build_option('color', 'bg_color_selector', false, ' ' )
	const bg_color_text = lib.build_option('text', 'bg_color', false, 'use picker or any valid css (like "background-image")')
	const bg_tip = lib.b('div')
	bg_tip.classList.add('tip')
	bg_tip.innerHTML = 'tip: having bloom filter enabled defaults the background to black'
	bg.append( bg_tip )
	bg.append( color_picker )
	bg.append( bg_color_text )
	// color_picker.addEventListener('change', e => {
	// 	bg_color_text.querySelector('input').value = color_picker.querySelector('input').value
	// 	gallery.render_shortcode('build_form')
	// })
	env_section.append( bg )

	// fog
	const fog = build_category('fog')
	const fog_on = lib.build_option('checkbox', 'has_fog', false, 'use fog', false, false )
	fog.append( fog_on )
	const fog_picker = lib.build_option('color', 'fog_color', false, ' ' )
	fog_picker.classList.add('contingent')
	fog.append( fog_picker )
	const density = lib.build_option('range', 'fog_density', 5, 'density', false, true, {
		min: 0,
		max: 10,
	})
	fog.append( density )
	const expl = lib.b('div')
	expl.classList.add('tip', 'active')
	expl.innerHTML = 'tip: match your background color to fog color for realism'
	fog.append( expl )
	// options.append( fog )
	env_section.append( fog )

	// snow
	const snow = build_category('snow')
	const snow_on = lib.build_option('checkbox', 'has_snow', false, 'gentle snow', false, false )
	snow.append( snow_on )
	const blizzard = lib.build_option('checkbox', 'has_blizzard', false, 'blizzard', false, false )
	snow.append( blizzard )
	env_section.append( snow )

	form.append( env_section )

	// misc section
	const misc_section = build_section('misc')
	const misc_expl = lib.b('div')
	misc_expl.innerText = `These are debugging options; most galleries should not use these.`
	misc_section.append( misc_expl )

	// const build_option = ( type, name, value, label, placeholder, contingent, attrs, checked ) => {
	const use_decode = lib.build_option('checkbox', 'use_mesh_decode', false, 'use decompress', false, false)
	misc_section.append( use_decode )

	form.append( misc_section )

	// end misc

	// end options
	// form.append( options )




	// gallery shortcode
	const shortcode = build_section('shortcode')
	const p_code = lib.b('p')
	shortcode.append( p_code )
	const text = lib.b('textarea', 'shortcode')
	text.placeholder = 'generated shortcode will appear here'
	text.setAttribute('readonly', true )
	p_code.append( text )
	shortcode.append( p_code )
	form.append( shortcode )

	// gallery save / close
	const manage = lib.b('p')
	manage.classList.add('threepress-form-controls')
	const save = lib.b('div', 'create-gallery', 'button', 'form-action')
	save.innerText = gallery.location === 'product' ? 'set' : 'save'

	const close = lib.b('div', 'close-gallery', 'threepress-cancel', 'form-action')
	close.innerText = gallery.location === 'product' ? 'cancel' : 'close'
	close.classList.add('button', 'button-primary')

	const menu_clar = lib.b('div')
	menu_clar.innerHTML = 'you do not have to save a shortcode to use it - saving is just for reference'
	menu_clar.classList.add('clarification')
	manage.append( close )
	manage.append( save )
	manage.append( menu_clar )
	form.append( manage )




	//////////////////////////////////////////////// bind


	// general updates

	form.addEventListener('click', e => {

		gallery.render_change( e.target, form, model_choice, ground_choice, ground_choice_map, shortcode )

		gallery.set_tooltips()

	})


	for( const input of form.querySelectorAll('input') ){
		input.addEventListener('change', e => {
			shortcode.value = gallery.render_shortcode() // gallery.form
		})
	}


	// custom updates

	// bg
	form.querySelector('input[name=bg_color]').addEventListener('keyup', e => {
		e.target.value = e.target.value.replace(/ /g, '')
	})
	const bg_picker = form.querySelector(".gallery-section input[name=bg_color_selector]")
	const bg_color = form.querySelector('input[name=bg_color]')
	bg_picker.addEventListener('change', e => {
		bg_color.value = bg_picker.value
		shortcode.value = gallery.render_shortcode() // gallery.form
	})

	// fog
	fog_picker.addEventListener('change', e => {
		shortcode.value = gallery.render_shortcode() // gallery.form
	})





	// form preview
	form.querySelector('#gallery-preview').addEventListener('click', () => {

		gallery.use_mesh_decode = gallery.form.querySelector('input[name=use_mesh_decode]')?.checked

		gallery.ingest_form( form )

		gallery.preview()

	})


	// saving

	save.addEventListener('click', e => {

		e.preventDefault()

		gallery.ingest_form()

		// console.log('saving: ', gallery.ground_dimensions, gallery.ground_coords )

		if( !gallery.validate( true, true, true )) return

		const shortcode_id = gallery.form.getAttribute('data-shortcode-id')

		const editing = shortcode_id ? true : false

		// console.log('saving shortcode id, shortcode ele', shortcode_id, shortcode )

		if( gallery.location === 'product' ){
			const metabox = output_container.parentElement.parentElement // document.querySelector('#threepress-product-options')
			output_container.innerHTML = ''
			const new_row = gallery.gen_row()
			output_container.append( new_row )
			gallery.form.style.display = 'none'
			window.scroll({
				top: window.pageYOffset + metabox.getBoundingClientRect().top - 120,
				behavior: 'smooth',
			})
			const toggle = document.querySelector('#threepress-product-options .inside>.button')
			if( toggle ) toggle.innerHTML = '+'
			return
		}

		// console.log( shortcode )
		const body = {
			action: 'threepress_save_shortcode',
			shortcode_id: shortcode_id,
			name: gallery.form.querySelector('input[name=gallery_name]').value.trim(),
			shortcode: shortcode.querySelector('textarea').value.trim(),
		}

		// console.log('saving post body: ', body )
		// console.log('saving shortcode: ', body.shortcode )

		lib.fetch_wrap( ajaxurl, 'post', body, false)
		.then( res => {

			if( res.success ){

				gallery.ingest_shortcode( res.gallery.shortcode )
				gallery.edited = res.gallery.edited
				gallery.created = res.gallery.created
				gallery.id = res.gallery.id 
				gallery.form.classList.add('editing')
				gallery.form.setAttribute('data-shortcode-id', res.gallery.id )
				if( output_container ){
					const new_row = gallery.gen_row()
					if( !output_container.getAttribute('data-stackable') ){ // product pages
						output_container.innerHTML = ''
						output_container.prepend( new_row )
						gallery.form.style.display = 'none'
					}else{ // admin pages
						const old_row = lib.get_row( document.querySelector('#model-galleries .content'), res.gallery.id )
						if( editing ){
							lib.insertAfter( new_row, old_row )
							old_row.remove()
						}else{
							output_container.prepend( new_row )
						}

					}

				}

				lib.hal('success', 'success', 5000 )

			}else{
				lib.hal('error', res.msg || 'error saving', 5000 )
				console.log( res )
			}
		})
		.catch( err => {
			lib.hal('error', err.msg || 'error saving', 5000 )
			console.log( err )
		})

	}) // save


	// closing
	close.addEventListener('click', e => {
		e.preventDefault()
		const target = gallery.form.parentElement
		gallery.form.style.display = 'none'
		const toggle = document.querySelector('#threepress-product-options .inside>.button')
		if( toggle ){
			toggle.innerHTML = '+'
			toggle.style.display = 'inline-block'
		}
		const anim_clips = form.querySelectorAll('#anim-clips .selection')
		for( const clip of anim_clips ) clip.remove()
		setTimeout( () => {
			const top = window.pageYOffset + target.getBoundingClientRect().top - 150
			window.scroll({
				top: top,
				behavior: 'smooth',
			})
		}, 100)
	})
	

	// ranges
	for( const input of gallery.form.querySelectorAll('input[type=range]') ){
		const tip = lib.b('div')
		tip.classList.add('range-tip')
		tip.style.display = 'none'
		input.parentElement.append( tip )
		let active = false
		input.addEventListener('mousedown', () => {
			tip.style.display = 'initial'
			active = true
		})
		input.addEventListener('mouseup', () => {
			tip.style.display = 'none'
			active = false
		})
		input.addEventListener('mouseout', () => {
			tip.style.display = 'none'
			active = false
		})
		input.addEventListener('mousemove', e => {
			if( !active ) return
			tip.style.top = ( e.clientY - 50 ) + 'px'
			tip.style.left = ( e.clientX + 10 ) + 'px'
			tip.innerHTML = input.value
		})
	}

	const skip_hide = [ /shortcode/i, /model/i, /name/i ]
	let regex, title, skip
	for( const section of gallery.form.querySelectorAll('.gallery-section') ){
		title = section.querySelector('h3').innerHTML
		skip = false
		for ( const r of skip_hide ){
			regex = new RegExp( r )
			if( title.match( regex ) ){
				skip  = true
			}
		}

		if( skip ) continue

		section.classList.add('threepress-section-hidden')

	}

	return form

}











