// Load plugins
import helper from "./helper";
import * as Popper from "@popperjs/core";
import dom from "@left4code/tw-starter/dist/js/dom";
import colors from "./colors";
import Chart from "chart.js/auto";
import axios from "axios";
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";


// Set plugins globally
window.helper = helper;
window.Popper = Popper;
window.$ = dom;
window.Chart = Chart;
window.colors = colors;
window.axios = axios;
window.xlsx = xlsx;
window.Tabulator = Tabulator;
