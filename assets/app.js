/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (full_base.html.twig).
 */

// any CSS you import will output into a single css file (app.scss in this case)
import './styles/app.scss';

import {tabler} from "@tabler/core/js/tabler";
global.tabler = tabler;


console.log('Hello Webpack Encore!');
