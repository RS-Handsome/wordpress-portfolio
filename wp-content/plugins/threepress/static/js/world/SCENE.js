import {
	FOG_COLOR,
	FOG_DENSITY,
} from '../lib.js?v=162'
import { 
	Scene, 
	Color, 
	AxesHelper,
	FogExp2, 
	// BoxBufferGeometry,
	// MeshLambertMaterial,
	// Mesh,
	// DoubleSide,
} from '../../inc/three.module.js?v=162'


const scene = new Scene()

scene.fog = new FogExp2( FOG_COLOR, FOG_DENSITY )

export default scene

