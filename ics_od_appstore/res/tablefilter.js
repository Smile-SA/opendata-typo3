/*------------------------------------------------------------------------
	- HTML Table Filter Generator v1.9.6
	- By Max Guglielmi (tablefilter.free.fr)
	- Licensed under the MIT License
--------------------------------------------------------------------------
Copyright (c) 2009 Max Guglielmi

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be included
in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
--------------------------------------------------------------------------
	- Special credit to: 
	Cedric Wartel, cnx.claude@free.fr, Florent Hirchy, Váry Péter, 
	Anthony Maes, Nuovella Williams, Fuggerbit, Venkata Seshagiri Rao 
	Raya for active contribution and inspiration
------------------------------------------------------------------------*/
/*
 * $Id: tablefilter.js 48432 2011-06-07 09:27:20Z emilieprudhomme $ 
 */

function setFilterGrid(id)
/*====================================================
	- Sets filters grid bar
	- Calls TF Constructor and generates grid bar
	- Params:
			- id: table id (string)
			- refRow (optional): row index (number)
			- config (optional): configuration 
			object (literal object)
=====================================================*/
{
	if( arguments.length==0 ) return;
	eval( 'tf_'+id+' = new TF(arguments[0],arguments[1],arguments[2])' );
	eval( 'tf_'+id+'.AddGrid();' );
}

/*===BEGIN removable section===========================
	- Unobtrusive grid bar generation using 
	'filterable' class
	- If you don't use it you can remove safely this 
	section
/*=====================================================*/
tf_addEvent(window, 'load', initFilterGrid);

function initFilterGrid()
{
	if (!document.getElementsByTagName) return;
	var tbls = tf_Tag(document,'table'), config;
	for (var i=0; i<tbls.length; i++)
	{
		var cTbl = tbls[i], cTblId = cTbl.getAttribute('id');
		if( tf_hasClass(cTbl,'filterable') && cTblId )
		{
			if( tf_isObj(cTblId+'_config') )
				config = eval(cTblId+'_config');
			else
				config = undefined;
			setFilterGrid( cTblId,config );
		}
	}// for i
}
/*===END removable section===========================*/

var TF = function( id )
/*====================================================
	- TF object constructor
	- Params:
			- id: table id (string)
			- refRow (optional): row index (number)
			- config (optional): configuration 
			object (literal object)
=====================================================*/
{
	if( arguments.length==0 ) return;
		
	this.id = id;
	this.tbl = tf_Id(id);
	this.startRow = undefined;
	this.refRow = null;
	this.headersRow = null;
	this.fObj = null;
	this.nbFilterableRows = null;
	this.nbRows = null;
	this.nbCells = null;
	this.hasGrid = false;
	
	if(this.tbl != null && this.tbl.nodeName.tf_LCase() == 'table' && this.GetRowsNb())
    {
		if(arguments.length>1)
        {
            for(var i=0; i<arguments.length; i++)
            {
                var argtype = typeof arguments[i];
               
                switch(argtype.tf_LCase())
                {
                    case 'number':
                        this.startRow = arguments[i];
                    break;
                    case 'object':
                        this.fObj = arguments[i];
                    break;
                }//switch                           
            }//for
        }//if
		
		var f = this.fObj;
		
		/*** filter types ***/
		this.fltTypeInp =			'input';
		this.fltTypeSlc =			'select';
		this.fltTypeMulti =			'multiple';
		this.fltTypeCheckList =		'checklist';
		this.fltTypeNone =			'none';
		
		/*** filters' grid properties ***/
		this.fltGrid = 				f!=undefined && f.grid==false ? false : true; //enables/disables filter grid
		
		/*** Grid layout ***/
		this.gridLayout = 			f!=undefined && f.grid_layout ? true : false; //enables/disables grid layout (fixed headers)
		this.gridWidth =			f!=undefined && f.grid_width!=undefined ? f.grid_width : null; //defines grid width
		this.gridHeight =			f!=undefined && f.grid_height!=undefined ? f.grid_height : null; //defines grid height
		this.gridMainContCssClass = f!=undefined && f.grid_cont_css_class!=undefined //defines css class for main container
										? f.grid_cont_css_class : 'grd_Cont';
		this.gridContCssClass =		f!=undefined && f.grid_tbl_cont_css_class!=undefined //defines css class for div containing table
										? f.grid_tbl_cont_css_class : 'grd_tblCont';
		this.gridHeadContCssClass = f!=undefined && f.grid_tblHead_cont_css_class!=undefined //defines css class for div containing headers' table
										? f.grid_tblHead_cont_css_class : 'grd_headTblCont';
		this.gridInfDivCssClass =	f!=undefined && f.grid_inf_grid_css_class!=undefined //defines css class for div containing rows counter, paging etc.
										? f.grid_inf_grid_css_class : 'grd_inf';
		this.gridHeadRowIndex =		f!=undefined && f.grid_headers_row_index!=undefined //defines which row contains column headers
										? f.grid_headers_row_index : 0; 
		this.gridHeadRows =			f!=undefined && f.grid_headers_rows!=undefined //array of headers row indexes to be placed in header table
										? f.grid_headers_rows : [0];
		this.gridEnableFilters =	f!=undefined && f.grid_enable_default_filters!=undefined 
										? f.grid_enable_default_filters : true; //generate filters in table headers
		this.gridDefaultColWidth =	f!=undefined && f.grid_default_col_width!=undefined 
										? f.grid_default_col_width : '100px'; //default col width						
		this.gridEnableColResizer =	f!=undefined && f.grid_enable_cols_resizer!=undefined 
										? f.grid_enable_cols_resizer : true; //enables/disables columns resizer
		this.hasGridWidthsRow =		false; //flag indicating if the grid has an additional row for column widths (IE<=7)
		this.gridColElms =			[];
		this.sourceTblHtml = 		this.tbl.outerHTML; //original table html												
		/*** ***/
								
		this.filtersRowIndex =		f!=undefined && f.filters_row_index!=undefined //defines in which row filters grid bar is generated
										? f.filters_row_index>1 ? 1 : f.filters_row_index : 0;
		this.fltCellTag =			f!=undefined && f.filters_cell_tag!=undefined //defines tag of the cells containing filters (td/th)
										? (f.filters_cell_tag!='th' ? 'td' : 'th') : 'td';		
		this.fltIds = 				[]; //stores filters ids
		this.searchArgs =			null; //stores filters values
		this.tblData =				[]; //stores table data
		this.validRowsIndex =		null; //stores valid rows indexes (rows visible upon filtering)
		this.fltGridEl =			null; //stores filters row element
		this.isFirstLoad =			true; //is first load boolean 
		this.infDiv =				null; //container div for paging elements, reset btn etc.
		this.lDiv =					null; //div for rows counter
		this.rDiv =					null; //div for reset button and results per page select
		this.mDiv =					null; //div for paging elements
		this.contDiv =				null; //table container div for fixed headers (IE only)
		this.infDivCssClass =		f!=undefined && f.inf_div_css_class!=undefined	//defines css class for div containing
										? f.inf_div_css_class : 'inf';				//paging elements, rows counter etc.
		this.lDivCssClass =			f!=undefined && f.left_div_css_class!=undefined	//defines css class for left div 
										? f.left_div_css_class : 'ldiv';
		this.rDivCssClass =			f!=undefined && f.right_div_css_class!=undefined //defines css class for right div 
										? f.right_div_css_class : 'rdiv';
		this.mDivCssClass =			f!=undefined && f.middle_div_css_class!=undefined //defines css class for mid div 
										? f.middle_div_css_class : 'mdiv';
		this.contDivCssClass =		f!=undefined && f.content_div_css_class!=undefined 
										? f.content_div_css_class : 'cont';	//table container div css class
		
		/*** filters' grid appearance ***/
		this.fltsRowCssClass =		f!=undefined && f.flts_row_css_class!=undefined //defines css class for filters row
										? f.flts_row_css_class : 'fltrow';		
		this.alternateBgs =			f!=undefined && f.alternate_rows ? true : false; //enables/disbles rows alternating bg colors
		this.hasColWidth =			f!=undefined && f.col_width ? true : false; //defines widths of columns
		this.colWidth =				f!=undefined && this.hasColWidth ? f.col_width : null;
		this.fixedHeaders =			f!=undefined && f.fixed_headers ? true : false; //enables/disables fixed headers
		this.tBodyH = 				f!=undefined && f.tbody_height ? f.tbody_height : 200; //tbody height if fixed headers enabled
		this.fltCssClass =			f!=undefined && f.flt_css_class!=undefined //defines css class for filters
										? f.flt_css_class : 'flt';
		this.fltMultiCssClass =		f!=undefined && f.flt_multi_css_class!=undefined //defines css class for multiple selects filters
										? f.flt_multi_css_class : 'flt_multi';
		this.fltSmallCssClass =		f!=undefined && f.flt_small_css_class!=undefined //defines css class for filters
										? f.flt_small_css_class : 'flt_s';
		this.singleFltCssClass =	f!=undefined && f.single_flt_css_class!=undefined //defines css class for single-filter
										? f.single_flt_css_class : 'single_flt';	
		this.isStartBgAlternate =	true;
		this.rowBgEvenCssClass =	f!=undefined && f.even_row_css_class!=undefined //defines css class for even rows
										? f.even_row_css_class :'even';
		this.rowBgOddCssClass =		f!=undefined && f.odd_row_css_class!=undefined //defines css class for odd rows
										? f.odd_row_css_class :'odd';
		
		/*** filters' grid behaviours ***/
		this.enterKey =				f!=undefined && f.enter_key==false ? false : true; //enables/disables enter key
		this.isModFilterFn = 		f!=undefined && f.mod_filter_fn ? true : false; //enables/disables alternative fn call		
		this.modFilterFn =			this.isModFilterFn ? f.mod_filter_fn : null;// used by tf_DetectKey fn
		this.onBeforeFilter =		f!=undefined && tf_isFn(f.on_before_filter) //calls function before filtering starts
										? f.on_before_filter : null;
		this.onAfterFilter =		f!=undefined && tf_isFn(f.on_after_filter) //calls function after filtering
										? f.on_after_filter : null;								
		this.matchCase =			f!=undefined && f.match_case ? true : false; //enables/disables case sensitivity
		this.exactMatch =			f!=undefined && f.exact_match ? true : false; //enables/disbles exact match for search
		this.refreshFilters =		f!=undefined && f.refresh_filters ? true : false; //refreshes drop-down lists upon validation
		this.activeFlt =			null; //stores active filter element
		this.activeFilterId =		null; //id of active filter
		this.hasColOperation =		f!=undefined && f.col_operation ? true : false; //enables/disbles column operation(sum,mean)
		this.colOperation =			null;
		this.hasVisibleRows = 		f!=undefined && f.rows_always_visible ? true : false; //enables always visible rows
		this.visibleRows =			this.hasVisibleRows ? f.rows_always_visible : [];//array containing always visible rows
		this.searchType =			f!=undefined && f.search_type!=undefined //defines search type: include or exclude
										? f.search_type : 'include';
		this.isExternalFlt =		f!=undefined && f.external_flt_grid ? true : false; //enables/disables external filters generation
		this.externalFltTgtIds =	f!=undefined && f.external_flt_grid_ids!=undefined //array containing ids of external elements containing filters
										? f.external_flt_grid_ids : null;
		this.externalFltEls =		[]; //stores filters elements if isExternalFlt is true		
		this.execDelay =			f!=undefined && f.exec_delay ? parseInt(f.exec_delay) : 100; //delays filtering process if loader true
		this.status =				f!=undefined && f.status ? true : false; //enables/disables status messages
		this.onFiltersLoaded =		f!=undefined && tf_isFn(f.on_filters_loaded) //calls function when filters grid loaded
										? f.on_filters_loaded : null;
		this.singleSearchFlt =		f!=undefined && f.single_search_filter ? true : false; //enables/disables single filter search
		this.onRowValidated =		f!=undefined && tf_isFn(f.on_row_validated) //calls function after row is validated
									 	? f.on_row_validated : null;
		this.customCellDataCols =	f!=undefined && f.custom_cell_data_cols ? f.custom_cell_data_cols : []; //array defining columns for customCellData event 	
		this.customCellData =		f!=undefined && tf_isFn(f.custom_cell_data) //calls custom function for retrieving cell data
									 	? f.custom_cell_data : null;																
		
		/*** selects customisation and behaviours ***/
		this.displayAllText =		f!=undefined && f.display_all_text!=undefined ? f.display_all_text : ''; //defines 1st option text
		this.onSlcChange = 			f!=undefined && f.on_change==false ? false : true; //enables/disables onChange event on combo-box 
		this.sortSlc =				f!=undefined && f.sort_select==false ? false : true; //enables/disables select options sorting
		this.isSortNumAsc =			f!=undefined && f.sort_num_asc ? true : false; //enables/disables ascending numeric options sorting
		this.sortNumAsc =			this.isSortNumAsc ? f.sort_num_asc : null;
		this.isSortNumDesc =		f!=undefined && f.sort_num_desc ? true : false; //enables/disables descending numeric options sorting
		this.sortNumDesc =			this.isSortNumDesc ? f.sort_num_desc : null;
		this.slcFillingMethod =		f!=undefined && f.slc_filling_method!=undefined //sets select filling method: 'innerHTML' or 
										? f.slc_filling_method : 'createElement';	//'createElement'
		this.fillSlcOnDemand =		f!=undefined && f.fill_slc_on_demand ? true : false; //enabled selects are populated on demand
		this.activateSlcTooltip =	f!=undefined && f.activate_slc_tooltip!=undefined //IE only, tooltip text appearing on select 
										? f.activate_slc_tooltip : 'Click to activate'; // before it is populated
		this.multipleSlcTooltip =	f!=undefined && f.multiple_slc_tooltip!=undefined //tooltip text appearing on multiple select 
										? f.multiple_slc_tooltip : 'Use Ctrl key for multiple selections';
		this.hasCustomSlcOptions =	f!=undefined && f.custom_slc_options &&
										(typeof f.custom_slc_options).tf_LCase() == 'object' 
										? true : false;	
		this.customSlcOptions =		f!=undefined && f.custom_slc_options!=undefined
										? f.custom_slc_options : null;
		this.onBeforeOperation =	f!=undefined && tf_isFn(f.on_before_operation) //calls function before col operation
										? f.on_before_operation : null;
		this.onAfterOperation =		f!=undefined && tf_isFn(f.on_after_operation) //calls function after col operation
										? f.on_after_operation : null;
		
		/*** checklist customisation and behaviours ***/
		this.checkListDiv = 		[]; //checklist container div
		this.checkListDivCssClass = f!=undefined && f.div_checklist_css_class!=undefined 
										? f.div_checklist_css_class : 'div_checklist'; //defines css class for div containing checklist filter
		this.checkListCssClass =	f!=undefined && f.checklist_css_class!=undefined //defines css class for checklist filters
										? f.checklist_css_class : 'flt_checklist';
		this.checkListItemCssClass = f!=undefined && f.checklist_item_css_class!=undefined //defines css class for checklist item (li)
										? f.checklist_item_css_class : 'flt_checklist_item';
		this.checkListSlcItemCssClass = f!=undefined && f.checklist_selected_item_css_class!=undefined //defines css class for selected checklist item (li)
										? f.checklist_selected_item_css_class : 'flt_checklist_slc_item';								
		this.activateCheckListTxt =	f!=undefined && f.activate_checklist_text!=undefined //Load on demand text 
										? f.activate_checklist_text : 'Click to load data';
		
		/*** Filter operators ***/
		this.orOperator =			f!=undefined && f.or_operator!=undefined ? f.or_operator : '||';
		this.anOperator =			f!=undefined && f.and_operator!=undefined ? f.and_operator : '&&';
		this.grOperator = 			f!=undefined && f.greater_operator!=undefined ? f.greater_operator : '>';
		this.lwOperator =			f!=undefined && f.lower_operator!=undefined ? f.lower_operator : '<';
		this.leOperator =			f!=undefined && f.lower_equal_operator!=undefined ? f.lower_equal_operator : '<=';
		this.geOperator =			f!=undefined && f.greater_equal_operator!=undefined ? f.greater_equal_operator : '>=';
		this.dfOperator =			f!=undefined && f.different_operator!=undefined ? f.different_operator : '!';
		this.lkOperator =			f!=undefined && f.like_operator!=undefined ? f.like_operator : '*';
		this.eqOperator =			f!=undefined && f.equal_operator!=undefined ? f.equal_operator : '=';
		this.stOperator =			f!=undefined && f.start_with_operator!=undefined ? f.start_with_operator : '{';
		this.enOperator =			f!=undefined && f.end_with_operator!=undefined ? f.end_with_operator : '}';
		this.curExp =				f!=undefined && f.cur_exp!=undefined ? f.cur_exp : '^[¥£€$]';
		this.separator = 			f!=undefined && f.separator!=undefined ? f.separator : ',';
		
		/*** rows counter ***/
		this.rowsCounter = 			f!=undefined && f.rows_counter ? true : false; //show/hides rows counter
		this.rowsCounterTgtId =		f!=undefined && f.rows_counter_target_id!=undefined //id of custom container element
										? f.rows_counter_target_id : null;
		this.rowsCounterDiv =		null; //element containing tot nb rows
		this.rowsCounterSpan =		null; //element containing tot nb rows label
		this.rowsCounterText =		f!=undefined && f.rows_counter_text!=undefined
										? f.rows_counter_text : 'Rows: '; //defines rows counter text
		this.totRowsCssClass =		f!=undefined && f.tot_rows_css_class!=undefined //defines css class rows counter
										? f.tot_rows_css_class : 'tot';		
		
		/*** status bar ***/
		this.statusBar =			f!=undefined && f.status_bar ? true : false; //show/hides status bar
		this.statusBarTgtId =		f!=undefined && f.status_bar_target_id!=undefined //id of custom container element
										? f.status_bar_target_id : null;
		this.statusBarDiv =			null; //element containing status bar label
		this.statusBarSpan =		null; //status bar
		this.statusBarSpanText =	null; //status bar label
		this.statusBarText =		f!=undefined && f.status_bar_text!=undefined
										? f.status_bar_text : ''; //defines status bar text
		this.statusBarCssClass =	f!=undefined && f.status_bar_css_class!=undefined //defines css class status bar
										? f.status_bar_css_class : 'status';
		this.statusBarCloseDelay =	250; //delay for status bar clearing			
		
		/*** loader ***/
		this.loader =				f!=undefined && f.loader ? true : false; //enables/disables loader
		this.loaderTgtId =			f!=undefined && f.loader_target_id!=undefined //id of container element
										? f.loader_target_id : null;
		this.loaderDiv =			null; //div containing loader
		this.loaderText =			f!=undefined && f.loader_text!=undefined ? f.loader_text : 'Loading...'; //defines loader text
		this.loaderHtml =			f!=undefined && f.loader_html!=undefined ? f.loader_html : null; //defines loader innerHtml
		this.loaderCssClass = 		f!=undefined && f.loader_css_class!=undefined //defines css class for loader div
										? f.loader_css_class : 'loader';
		this.loaderCloseDelay =		200; //delay for hiding loader
		this.onShowLoader =			f!=undefined && tf_isFn(f.on_show_loader) //calls function before loader is displayed
										? f.on_show_loader : null;
		this.onHideLoader =			f!=undefined && tf_isFn(f.on_hide_loader) //calls function after loader is closed
										? f.on_hide_loader : null;					
		
		/*** validation - reset buttons/links ***/
		this.displayBtn =			f!=undefined && f.btn ? true : false; //show/hides filter's validation button
		this.btnText =				f!=undefined && f.btn_text!=undefined ? f.btn_text : 'go'; //defines validation button text
		this.btnCssClass =			f!=undefined && f.btn_css_class!=undefined //defines css class for validation button
										? f.btn_css_class : 'btnflt';
		this.btnReset = 			f!=undefined && f.btn_reset ? true : false; //show/hides reset link
		this.btnResetTgtId =		f!=undefined && f.btn_reset_target_id!=undefined //id of container element
										? f.btn_reset_target_id : null;
		this.btnResetEl =			null; //reset button element
		this.btnResetText =			f!=undefined && f.btn_reset_text!=undefined ? f.btn_reset_text : 'Reset'; //defines reset text
		this.btnResetHtml = 		f!=undefined && f.btn_reset_html!=undefined ? f.btn_reset_html : null; //defines reset button innerHtml
		this.btnResetCssClass =		f!=undefined && f.btn_reset_css_class!=undefined //defines css class for reset button
										? f.btn_reset_css_class :'reset';
		
		/*** paging ***/
		this.paging =				f!=undefined && f.paging ? true : false; //enables/disables table paging
		this.pagingTgtId =			f!=undefined && f.paging_target_id!=undefined //id of container element
										? f.paging_target_id : null;		
		this.pagingLength =			f!=undefined && f.paging_length!=undefined ? f.paging_length : 10; //defines table paging length
		this.hasResultsPerPage =	f!=undefined && f.results_per_page ? true : false; //enables/disables results per page drop-down
		this.resultsPerPageTgtId =	f!=undefined && f.results_per_page_target_id!=undefined //id of container element
										? f.results_per_page_target_id : null;	
		this.resultsPerPage =		null; //stores results per page text and values			
		this.pagingSlc =			null; //stores paging select element
		this.isPagingRemoved =		false; //indicates if paging elements were previously removed
		this.pgSlcCssClass =		f!=undefined && f.paging_slc_css_class!=undefined
										? f.paging_slc_css_class :'pgSlc'; //css class for paging select element
		this.pgInpCssClass =		f!=undefined && f.paging_inp_css_class!=undefined
										? f.paging_inp_css_class :'pgNbInp'; //css class for paging input element
		this.resultsPerPageSlc =	null; //results per page select element
		this.resultsSlcCssClass =	f!=undefined && f.results_slc_css_class!=undefined
										? f.results_slc_css_class :'rspg'; //defines css class for results per page select
		this.resultsSpanCssClass =	f!=undefined && f.results_span_css_class!=undefined
										? f.results_span_css_class :'rspgSpan'; //css class for label preceding results per page select
		this.nbVisibleRows	=		0; //nb visible rows
		this.nbHiddenRows =			0; //nb hidden rows
		this.startPagingRow =		0; //1st row index of current page
		this.nbPages = 				0; //total nb of pages
		this.currentPageNb =		1; //current page nb
		this.btnNextPageText = 		f!=undefined && f.btn_next_page_text!=undefined
										? f.btn_next_page_text : '>'; //defines next page button text
		this.btnPrevPageText =		f!=undefined && f.btn_prev_page_text!=undefined
										? f.btn_prev_page_text : '<'; //defines previous page button text
		this.btnLastPageText =		f!=undefined && f.btn_last_page_text!=undefined
										? f.btn_last_page_text : '>|'; //defines last page button text
		this.btnFirstPageText =		f!=undefined && f.btn_first_page_text!=undefined
										? f.btn_first_page_text : '|<' ; //defines first page button text
		this.btnNextPageHtml =		f!=undefined && f.btn_next_page_html!=undefined
										? f.btn_next_page_html : null; //defines next page button html
		this.btnPrevPageHtml =		f!=undefined && f.btn_prev_page_html!=undefined
										? f.btn_prev_page_html : null; //defines previous page button html
		this.btnFirstPageHtml =		f!=undefined && f.btn_first_page_html!=undefined
										? f.btn_first_page_html : null; //defines last page button html
		this.btnLastPageHtml =		f!=undefined && f.btn_last_page_html!=undefined
										? f.btn_last_page_html : null; //defines previous page button html
		this.btnPageCssClass =		f!=undefined && f.paging_btn_css_class!=undefined
										? f.paging_btn_css_class :'pgInp'; //css class for paging buttons (previous,next,etc.)
		this.nbPgSpanCssClass = 	f!=undefined && f.nb_pages_css_class!=undefined
										? f.nb_pages_css_class :'nbpg'; //css class for span containing tot nb of pages
		this.hasPagingBtns =		f!=undefined && f.paging_btns==false ? false : true; //enables/disables paging buttons
		this.pagingBtnEvents =		null; //stores paging buttons events
		this.pageSelectorType =		f!=undefined && f.page_selector_type!=undefined
										? f.page_selector_type : this.fltTypeSlc; //defines previous page button html		
		
		/*** webfx sort adapter ***/
		this.sort =					f!=undefined && f.sort ? true : false; //enables/disables default table sorting
		this.isSortEnabled =		false; //indicates if sort is set (used in tfAdapter.sortabletable.js)
		this.sorted =				false; //indicates if tables was sorted
		this.sortConfig =			f!=undefined && f.sort_config!=undefined 
										? f.sort_config : {};
		this.sortConfig.name =		f!=undefined && f.sort_config!=undefined && f.sort_config.name
										? f.sort_config.name : 'sortabletable';
		this.sortConfig.src =		f!=undefined && f.sort_config!=undefined && f.sort_config.src
										? f.sort_config.src : 'sortabletable.js';
		this.sortConfig.adapterSrc =f!=undefined && f.sort_config!=undefined && f.sort_config.adapter_src
										? f.sort_config.adapter_src : 'tfAdapter.sortabletable.js';
		this.sortConfig.initialize =f!=undefined && f.sort_config!=undefined && f.sort_config.initialize
										? f.sort_config.initialize
										: function(o){ if(o.SetSortTable) o.SetSortTable(); };
		this.sortConfig.sortTypes =	f!=undefined && f.sort_config!=undefined && f.sort_config.sort_types
										? f.sort_config.sort_types : [];
		this.sortConfig.sortCol =	f!=undefined && f.sort_config!=undefined && f.sort_config.sort_col!=undefined
										? f.sort_config.sort_col : null;
		this.sortConfig.asyncSort =	f!=undefined && f.sort_config!=undefined && f.sort_config.async_sort
										? true : false;
		this.sortConfig.triggerIds =f!=undefined && f.sort_config!=undefined && f.sort_config.sort_trigger_ids
										? f.sort_config.sort_trigger_ids : [];									
		
		/*** onkeyup event ***/
		this.onKeyUp =				f!=undefined && f.on_keyup ? true : false; //enables/disables onkeyup event, table is filtered when user stops typing
		this.onKeyUpDelay =			f!=undefined && f.on_keyup_delay!=undefined ? f.on_keyup_delay : 900; //onkeyup delay timer (msecs)
		this.isUserTyping = 		null; //typing indicator
		this.onKeyUpTimer = 		undefined;		
		
		/*** keyword highlighting ***/
		this.highlightKeywords = 	f!=undefined && f.highlight_keywords ? true : false; //enables/disables keyword highlighting
		this.highlightCssClass =	f!=undefined && f.highlight_css_class!=undefined //defines css class for highlighting
										? f.highlight_css_class : 'keyword';	
		
		/*** data types ***/
		this.defaultDateType =		f!=undefined && f.default_date_type!=undefined //defines default date type (european DMY)
										? f.default_date_type : 'DMY';
		this.thousandsSeparator =	f!=undefined && f.thousands_separator!=undefined //defines default thousands separator 
										? f.thousands_separator : ','; //US = ',' EU = '.'
		this.decimalSeparator = 	f!=undefined && f.decimal_separator!=undefined //defines default decimal separator 
										? f.decimal_separator : '.'; //US & javascript = '.' EU = ','
		this.hasColNbFormat = 		f!=undefined && f.col_number_format ? true : false; //enables number format per column
		this.colNbFormat = 			f!=undefined && this.hasColNbFormat ? f.col_number_format : null; //array containing columns nb formats
		this.hasColDateType = 		f!=undefined && f.col_date_type ? true : false; //enables date type per column
		this.colDateType =			f!=undefined && this.hasColDateType ? f.col_date_type : null; //array containing columns date type
		
		/*** status messages ***/
		this.msgFilter =			f!=undefined && f.msg_filter!=undefined //filtering
										? f.msg_filter : 'Filtering data...'; 
		this.msgPopulate =			f!=undefined && f.msg_populate!=undefined //populating drop-downs
										? f.msg_populate : 'Populating filter...'; 
		this.msgPopulateCheckList =	f!=undefined && f.msg_populate_checklist!=undefined //populating drop-downs
										? f.msg_populate_checklist : 'Populating list...'; 
		this.msgChangePage =		f!=undefined && f.msg_change_page!=undefined //changing paging page
										? f.msg_change_page : 'Collecting paging data...';
		this.msgClear =				f!=undefined && f.msg_clear!=undefined //clearing filters
										? f.msg_clear : 'Clearing filters...';
		this.msgChangeResults =		f!=undefined && f.msg_change_results!=undefined //changing nb results/page
										? f.msg_change_results : 'Changing results per page...';
		this.msgResetValues =		f!=undefined && f.msg_reset_grid_values!=undefined //re-setting grid values
										? f.msg_reset_grid_values : 'Re-setting filters values...';
		this.msgResetPage =			f!=undefined && f.msg_reset_page!=undefined //re-setting page
										? f.msg_reset_page : 'Re-setting page...';
		this.msgResetPageLength =	f!=undefined && f.msg_reset_page_length!=undefined //re-setting page length
										? f.msg_reset_page_length : 'Re-setting page length...';
		this.msgSort =				f!=undefined && f.msg_sort!=undefined //table sorting
										? f.msg_sort : 'Sorting data...';
		this.msgLoadExtensions =	f!=undefined && f.msg_load_extensions!=undefined //table sorting
										? f.msg_load_extensions : 'Loading extensions...';			

		/*** ids prefixes ***/
		this.prfxFlt =				'flt'; //filters (inputs - selects)
		this.prfxValButton =		'btn'; //validation button
		this.prfxInfDiv =			'inf_'; //container div for paging elements, rows counter etc.
		this.prfxLDiv =				'ldiv_'; //left div
		this.prfxRDiv =				'rdiv_'; //right div
		this.prfxMDiv =				'mdiv_'; //middle div
		this.prfxContentDiv =		'cont_'; //table container if fixed headers enabled
		this.prfxCheckListDiv =		'chkdiv_'; //checklist filter container div
		this.prfxSlcPages =			'slcPages_'; //pages select
		this.prfxSlcResults = 		'slcResults_'; //results per page select
		this.prfxSlcResultsTxt =	'slcResultsTxt_'; //label preciding results per page select	
		this.prfxBtnNextSpan =		'btnNextSpan_'; //span containing next page button
		this.prfxBtnPrevSpan =		'btnPrevSpan_'; //span containing previous page button
		this.prfxBtnLastSpan =		'btnLastSpan_'; //span containing last page button
		this.prfxBtnFirstSpan =		'btnFirstSpan_'; //span containing first page button
		this.prfxBtnNext =			'btnNext_'; //next button
		this.prfxBtnPrev =			'btnPrev_'; //previous button
		this.prfxBtnLast =			'btnLast_'; //last button
		this.prfxBtnFirst =			'btnFirst_'; //first button
		this.prfxPgSpan =			'pgspan_'; //span for tot nb pages
		this.prfxPgBeforeSpan =		'pgbeforespan_'; //span preceding pages select (contains 'Page')
		this.prfxPgAfterSpan =		'pgafterspan_'; //span following pages select (contains ' of ')
		this.prfxCounter =			'counter_'; //rows counter div
		this.prfxTotRows =			'totrows_span_'; //nb displayed rows label
		this.prfxTotRowsTxt =		'totRowsTextSpan_'; //label preceding nb rows label
		this.prfxResetSpan =		'resetspan_'; //span containing reset button
		this.prfxLoader =			'load_'; //loader div
		this.prfxStatus =			'status_'; //status bar div
		this.prfxStatusSpan =		'statusSpan_'; //status bar label
		this.prfxStatusTxt =		'statusText_';//text preceding status bar label
		this.prfxCookieFltsValues =	'tf_flts_'; //filter values cookie
		this.prfxCookiePageNb =		'tf_pgnb_'; //page nb cookie
		this.prfxCookiePageLen = 	'tf_pglen_'; //page length cookie
		this.prfxMainTblCont =		'gridCont_'; //div containing grid elements if grid_layout true
		this.prfxTblCont =			'tblCont_'; //div containing table if grid_layout true
		this.prfxHeadTblCont = 		'tblHeadCont_'; //div containing headers table if grid_layout true
		this.prfxHeadTbl =			'tblHead_';	//headers' table if grid_layout true
		this.prfxGridFltTd =		'_td_'; //id of td containing the filter if grid_layout true
		this.prfxGridTh =			'tblHeadTh_'; //id of th containing column header if grid_layout true				

		/*** cookies ***/
		this.hasStoredValues =		false;
		this.rememberGridValues =	f!=undefined && f.remember_grid_values ? true : false; //remembers filters values on page load
		this.fltsValuesCookie =		this.prfxCookieFltsValues + this.id; //cookie storing filter values
		this.rememberPageNb =		this.paging && f!=undefined && f.remember_page_number
										? true : false; //remembers page nb on page load	
		this.pgNbCookie =			this.prfxCookiePageNb + this.id; //cookie storing page nb
		this.rememberPageLen =		this.paging && f!=undefined && f.remember_page_length
										? true : false; //remembers page length on page load
		this.pgLenCookie =			this.prfxCookiePageLen + this.id; //cookie storing page length
		this.cookieDuration =		f!=undefined && f.set_cookie_duration 
										? parseInt(f.set_cookie_duration) :100000; //cookie duration
		
		/*** extensions ***/
		this.hasExtensions =		f!=undefined && f.extensions ? true : false; //imports external script
		this.extensions =			(this.hasExtensions) ? f.extensions : null;

		/***(deprecated: backward compatibility) ***/
		this.hasBindScript =		f!=undefined && f.bind_script ? true : false; //imports external script
		this.bindScript =			(this.hasBindScript) ? f.bind_script : null;
		
		/*** TF events ***/
		var o = this;
		this.Evt = {
			name: {
				filter: 'Filter',
				populateselect: 'Populate',
				populatechecklist: 'PopulateCheckList',
				changepage: 'ChangePage',
				clear: 'Clear',
				changeresultsperpage: 'ChangeResults',
				resetvalues: 'ResetValues',
				resetpage: 'ResetPage',
				resetpagelength: 'ResetPageLength',
				sort: 'Sort',
				loadextensions: 'LoadExtensions'			
			},
			_DetectKey: function(e)
			/*====================================================
				- common fn that detects return key for a given
				element (onkeypress for inputs)
			=====================================================*/
			{
				if(!o.enterKey) return;
				var evt=(e)?e:(window.event)?window.event:null;
				if(evt)
				{
					var key=(evt.charCode)?evt.charCode:
						((evt.keyCode)?evt.keyCode:((evt.which)?evt.which:0));
					if(key=='13')
					{
						o.Filter();
					} else { 
						o.isUserTyping = true;
						window.clearInterval(o.onKeyUpTimer);
						o.onKeyUpTimer = undefined; 
					}
				}//if evt
			},
			_OnKeyUp: function(e)
			/*====================================================
				- onkeyup event for text filters 
				(onKeyUp property)
			=====================================================*/
			{
				if(!o.onKeyUp) return;
				var evt=(e)?e:(window.event)?window.event:null;
				var key=(evt.charCode)?evt.charCode:
						((evt.keyCode)?evt.keyCode:((evt.which)?evt.which:0));
				o.isUserTyping = false;
				
				if( key!=13 && key!=9 && key!=27 && key!=38 && key!=40 )
				{
					function filter()
					{
						window.clearInterval(o.onKeyUpTimer);
						o.onKeyUpTimer = undefined;
						if( !o.isUserTyping )
						{
							o.Filter();
							o.isUserTyping = null;			
						}
					}
					if(o.onKeyUpTimer==undefined)
						o.onKeyUpTimer = window.setInterval( filter, o.onKeyUpDelay );
				} else { 
					window.clearInterval(o.onKeyUpTimer); 
					o.onKeyUpTimer = undefined; 
				}
			},
			_OnKeyDown: function(e)
			/*====================================================
				- onkeydown event for input filters 
				(onKeyUp property)
			=====================================================*/
			{
				if(!o.onKeyUp) return;
				o.isUserTyping = true;
			},
			_OnInpBlur: function(e)
			/*====================================================
				- onblur event for input filters (onKeyUp property)
			=====================================================*/
			{
				if(!o.onKeyUp) return;
				o.isUserTyping = false; 
				window.clearInterval(o.onKeyUpTimer);
			},
			_OnInpFocus: function()
			/*====================================================
				- onfocus event for input filters
			=====================================================*/
			{
				o.activeFilterId=this.getAttribute('id');
				o.activeFlt = tf_Id(o.activeFilterId);
			},
			_OnSlcFocus: function()
			/*====================================================
				- onfocus event for select filters
			=====================================================*/
			{
				o.activeFilterId = this.getAttribute('id');
				o.activeFlt = tf_Id(o.activeFilterId);
				if(o.fillSlcOnDemand && this.getAttribute('filled') == '0')
				{// select is populated when element has focus
					var ct = this.getAttribute('ct');
					o.PopulateSelect(ct);
					if(!tf_isIE) this.setAttribute('filled','1');
				}
			},
			_OnSlcChange: function()
			/*====================================================
				- onchange event for select filters
			=====================================================*/
			{
				if(o.onSlcChange) o.Filter();
			},
			_OnSlcBlur: function()
			/*====================================================
				- onblur event for select filters
			=====================================================*/
			{
			},
			_OnCheckListClick: function()
			/*====================================================
				- onclick event for checklist filters
			=====================================================*/
			{
				if(o.fillSlcOnDemand && this.getAttribute('filled') == '0')
				{
					var ct = this.getAttribute('ct');
					o.PopulateCheckList(ct);
					o.checkListDiv[ct].onclick = null;
					o.checkListDiv[ct].title = '';
				}
			},
			_OnCheckListFocus: function()
			/*====================================================
				- onclick event for checklist filter container
			=====================================================*/
			{
				o.activeFilterId = this.firstChild.getAttribute('id');
				o.activeFlt = tf_Id(o.activeFilterId);
			},
			_OnBtnClick: function()
			/*====================================================
				- onclick event for validation button 
				(btn property)
			=====================================================*/
			{
				o.Filter();
			},
			_OnSlcPagesChange: function()
			/*====================================================
				- onchange event for paging select
			=====================================================*/
			{
				if(o.Evt._Paging._OnSlcPagesChangeEvt)
					o.Evt._Paging._OnSlcPagesChangeEvt();
				o.ChangePage();
				this.blur();
				//ie only: blur is not enough...
				if(this.parentNode && tf_isIE)
					this.parentNode.focus();
			},
			_OnSlcPagesChangeEvt: null, //used by sort adapter
			_OnSlcResultsChange: function()
			/*====================================================
				- onchange event for results per page select
			=====================================================*/
			{
				o.ChangeResultsPerPage();
				this.blur();
				//ie only: blur is not enough...
				if(this.parentNode && tf_isIE) 
					this.parentNode.focus();
			},
			_Paging: {// paging buttons events
				slcIndex: function(){ 
					return (o.pageSelectorType==o.fltTypeSlc) 
						? o.pagingSlc.options.selectedIndex 
						: parseInt(o.pagingSlc.value)-1;
				},
				nbOpts: function(){ 
					return (o.pageSelectorType==o.fltTypeSlc) 
					? parseInt(o.pagingSlc.options.length)-1 
					: (o.nbPages-1);
				},
				next: function(){
					if(o.Evt._Paging.nextEvt) o.Evt._Paging.nextEvt();
					var nextIndex = (o.Evt._Paging.slcIndex()<o.Evt._Paging.nbOpts()) 
						? o.Evt._Paging.slcIndex()+1 : 0;
					o.ChangePage(nextIndex);
				},
				nextEvt: null, //used by sort adapter
				prev: function(){
					if(o.Evt._Paging.prevEvt) o.Evt._Paging.prevEvt();
					var prevIndex = o.Evt._Paging.slcIndex()>0 
						? o.Evt._Paging.slcIndex()-1 : o.Evt._Paging.nbOpts();
					o.ChangePage(prevIndex);
				},
				prevEvt: null, //used by sort adapter
				last: function(){
					if(o.Evt._Paging.lastEvt) o.Evt._Paging.lastEvt();
					o.ChangePage(o.Evt._Paging.nbOpts());
				},
				lastEvt: null, //used by sort adapter
				first: function(){
					if(o.Evt._Paging.firstEvt)  o.Evt._Paging.firstEvt();
					o.ChangePage(0);
				},
				firstEvt: null, //used by sort adapter
				_detectKey: function(e)
				{
					var evt=(e)?e:(window.event)?window.event:null;
					if(evt)
					{
						var key=(evt.charCode)?evt.charCode:
							((evt.keyCode)?evt.keyCode:((evt.which)?evt.which:0));
						if(key=='13'){ 
							if(o.sorted){ o.Filter(); o.ChangePage(o.Evt._Paging.slcIndex()); }
							else o.ChangePage();								
							this.blur(); 
						}
					}//if evt
				}
			},
			_EnableSlc: function()
			/*====================================================
				- onclick event slc parent node (enables filters)
				IE only
			=====================================================*/
			{
				this.firstChild.disabled = false;							
				this.firstChild.focus();							
				this.onclick = null;
			},
			_Clear: function()
			/*====================================================
				- clears filters
			=====================================================*/
			{
				o.ClearFilters();
			},
			_EnableSort: function()
			/*====================================================
				- enables table sorting
			=====================================================*/
			{
				if(tf_isImported(o.sortConfig.adapterSrc))
					o.sortConfig.initialize.call(null,o);
				else
					o.IncludeFile(
						o.sortConfig.name+'_adapter',
						o.sortConfig.adapterSrc,
						function(){ o.sortConfig.initialize.call(null,o); }
					);
			}
		};
		
		/*** TF extensions ***/
		this.Ext = {
			list: {},
			add: function(extName, extDesc, extPath, extCallBack)
			{
				var file = extPath.split('/')[extPath.split('/').length-1];
				var re = new RegExp(file);
				var path = extPath.replace(re,'');
				o.Ext.list[extName] = { 
					name: extName,
					description: extDesc,
					file: file,
					path: path,
					callback: extCallBack
				};
			}
		};
		
    }//if tbl!=null		
}

TF.prototype = {
	
	AddGrid: function()
	/*====================================================
		- adds row with filtering grid bar and sets grid 
		behaviours and layout
	=====================================================*/
	{
		if(this.hasGrid) return;
		this.refRow = this.startRow==undefined ? 2 : (this.startRow+1);
		if(this.gridLayout) this.refRow = this.startRow==undefined ? 0 : this.startRow;
		this.headersRow = (this.filtersRowIndex==0) ? 1 : 0;
		try{ this.nbCells = this.GetCellsNb(this.refRow) }
		catch(e){ this.nbCells = this.GetCellsNb(0) }

		var f = this.fObj==undefined ? {} : this.fObj;
		var n = (this.singleSearchFlt) ? 1 : this.nbCells, inpclass;
		
		if(this.gridLayout)
		{
			this.isExternalFlt = true;
			this.SetGridLayout();
			//Once grid generated 1st filterable row is 0 again
			this.refRow = (tf_isIE || tf_isIE7) ? (this.refRow+1) : 0;
		}
		
		if(this.loader) this.SetLoader();
	
		if(this.hasResultsPerPage)
		{ 
			this.resultsPerPage = f['results_per_page']!=undefined   
				? f['results_per_page'] : this.resultsPerPage;
			if(this.resultsPerPage.length<2)
				this.hasResultsPerPage = false;
			else
				this.pagingLength = this.resultsPerPage[1][0];
		}
		
		if(!this.fltGrid)
		{//filters grid is not genetared
			this.refRow = (this.refRow-1);
			if(this.gridLayout) this.refRow = 0;
			this.nbFilterableRows = this.GetRowsNb();
			this.nbVisibleRows = this.nbFilterableRows;
			this.nbRows = this.nbFilterableRows;
		} else {
			if(this.isFirstLoad)
			{
				if(!this.gridLayout){
					var fltrow;
					var thead = tf_Tag(this.tbl,'thead');
					if( thead.length>0 )
						fltrow = thead[0].insertRow(this.filtersRowIndex);
					else
						fltrow = this.tbl.insertRow(this.filtersRowIndex);
					
					if(this.fixedHeaders) this.SetFixedHeaders();
					
					fltrow.className = this.fltsRowCssClass;
					//Disable for grid_layout
					if( this.isExternalFlt && !this.gridLayout ) fltrow.style.display = 'none';
				}
				
				this.nbFilterableRows = this.GetRowsNb();
				this.nbVisibleRows = this.nbFilterableRows;
				this.nbRows = this.tbl.rows.length;
				
				for(var i=0; i<n; i++)// this loop adds filters
				{
					var fltcell = tf_CreateElm(this.fltCellTag);
					if(this.singleSearchFlt) fltcell.colSpan = this.nbCells;
					if(!this.gridLayout) fltrow.appendChild( fltcell );
					inpclass = (i==n-1 && this.displayBtn) ? this.fltSmallCssClass : this.fltCssClass;
					
					if( this['col'+i]==undefined )
						this['col'+i] = (f['col_'+i]==undefined) 
							? this.fltTypeInp : f['col_'+i].tf_LCase();
							
					if(this.singleSearchFlt)
					{//only 1 input for single search
						this['col'+i] = this.fltTypeInp;
						inpclass = this.singleFltCssClass;
					}
	
					if(this['col'+i]==this.fltTypeSlc || this['col'+i]==this.fltTypeMulti)
					{//selects					
						var slc = tf_CreateElm( this.fltTypeSlc,
							['id',this.prfxFlt+i+'_'+this.id],
							['ct',i],['filled','0'] );
						if(this['col'+i]==this.fltTypeMulti)
						{
							slc.multiple = this.fltTypeMulti;
							slc.title = this.multipleSlcTooltip;
						}
						slc.className = (this['col'+i].tf_LCase()==this.fltTypeSlc) 
							? inpclass : this.fltMultiCssClass;// for ie<=6
						
						if( this.isExternalFlt && this.externalFltTgtIds && tf_Id(this.externalFltTgtIds[i]) )
						{//filter is appended in desired element
							tf_Id( this.externalFltTgtIds[i] ).appendChild(slc);
							this.externalFltEls.push(slc);
						} else {
							fltcell.appendChild(slc);
						}
						
						this.fltIds.push(this.prfxFlt+i+'_'+this.id);
						
						if(!this.fillSlcOnDemand) this.PopulateSelect(i);
						
						slc.onkeypress = this.Evt._DetectKey;
						slc.onchange = this.Evt._OnSlcChange;
						slc.onfocus = this.Evt._OnSlcFocus;
						slc.onblur = this.Evt._OnSlcBlur;
						
						if(this.fillSlcOnDemand)
						{//1st option is created here since PopulateSelect isn't invoked
							var opt0 = tf_CreateOpt(this.displayAllText,'');
							slc.appendChild( opt0 );						
						}
						
						/* 	Code below for IE: it prevents select options to
							slide out before select it-self is populated.
							This is an unexpeted behavior for users since at
							1st click options are empty. Work around: 
							select is disabled and by clicking on element 
							(parent td), users enable drop-down and select is
							populated at same time.  */
						if( this.fillSlcOnDemand && tf_isIE)
						{
							slc.disabled = true;
							slc.title = this.activateSlcTooltip;
							slc.parentNode.onclick = this.Evt._EnableSlc;
							if( this['col'+i]==this.fltTypeMulti)
								this.__deferMultipleSelection(slc,0);
						}
					}
					
					else if( this['col'+i]==this.fltTypeCheckList )
					{// checklist
						var divCont = tf_CreateElm('div',
										['id',this.prfxCheckListDiv+i+'_'+this.id],
										['ct',i],['filled','0'] );
						divCont.className = this.checkListDivCssClass;
						
						if( this.isExternalFlt && this.externalFltTgtIds 
							&& tf_Id(this.externalFltTgtIds[i]) )
						{//filter is appended in desired element
							tf_Id( this.externalFltTgtIds[i] ).appendChild(divCont);
							this.externalFltEls.push(divCont);
						} else {
							fltcell.appendChild(divCont);
						}
						
						this.checkListDiv[i] = divCont;
						this.fltIds.push(this.prfxFlt+i+'_'+this.id);
						if(!this.fillSlcOnDemand) this.PopulateCheckList(i);
						
						divCont.onclick = this.Evt._OnCheckListFocus;
						
						if(this.fillSlcOnDemand)
						{
							divCont.onclick = this.Evt._OnCheckListClick;
							divCont.appendChild(tf_CreateText(this.activateCheckListTxt));
						}
					}
					
					else
					{
						var inptype;
						(this['col'+i]==this.fltTypeInp) ? inptype='text' : inptype='hidden';//show/hide input	
						var inp = tf_CreateElm( this.fltTypeInp,['id',this.prfxFlt+i+'_'+this.id],['type',inptype],['ct',i] );					
						inp.className = inpclass;// for ie<=6
						inp.onfocus = this.Evt._OnInpFocus;
						
						if( this.isExternalFlt && this.externalFltTgtIds && tf_Id(this.externalFltTgtIds[i]) )
						{//filter is appended in desired element
							tf_Id( this.externalFltTgtIds[i] ).appendChild(inp);
							this.externalFltEls.push(inp);
						} else {
							fltcell.appendChild(inp);
						}
						
						this.fltIds.push(this.prfxFlt+i+'_'+this.id);
						
						inp.onkeypress = this.Evt._DetectKey;
						inp.onkeydown = this.Evt._OnKeyDown;
						inp.onkeyup = this.Evt._OnKeyUp;
						inp.onblur = this.Evt._OnInpBlur;
						
						if(this.rememberGridValues)
						{
							var flts = tf_ReadCookie(this.fltsValuesCookie); //reads the cookie
							var reg = new RegExp(',','g');
							var flts_values = flts.split(reg); //creates an array with filters' values
							if (flts_values[i]!=' ')
								this.SetFilterValue(i,flts_values[i],false);					
						}
					}
					
					if(i==n-1 && this.displayBtn)// this adds validation button
					{
						var btn = tf_CreateElm( this.fltTypeInp,['id',this.prfxValButton+i+'_'+this.id],
												['type','button'], ['value',this.btnText] );
						btn.className = this.btnCssClass;
						
						if( this.isExternalFlt && this.externalFltTgtIds && tf_Id(this.externalFltTgtIds[i]) ) 
						//filter is appended in desired element
							tf_Id( this.externalFltTgtIds[i] ).appendChild(btn);
						else
							fltcell.appendChild(btn);
						
						btn.onclick = this.Evt._OnBtnClick;				
					}//if
					
				}// for i
				
			} else {
				this.__resetGrid();			
			}//if isFirstLoad
		}//if this.fltGrid
		
		/* Filter behaviours */
		if(this.rowsCounter) this.SetRowsCounter();
		if(this.statusBar) this.SetStatusBar();
		if(this.fixedHeaders && !this.isFirstLoad) this.SetFixedHeaders();
		if(this.paging)	this.SetPaging();
		if(this.hasResultsPerPage && this.paging) this.SetResultsPerPage();
		if(this.btnReset) this.SetResetBtn();
		
		if(this.hasColWidth && !this.gridLayout) this.SetColWidths();
		
		if( this.alternateBgs && this.isStartBgAlternate )
			this.SetAlternateRows(); //1st time only if no paging and rememberGridValues
		
		if(this.hasColOperation && this.fltGrid)
		{
			this.colOperation = f.col_operation;
			this.SetColOperation();
		}
		
		if(this.sort) this.SetSort();
		
		/* Deprecated Loads external script */
		if(this.hasBindScript)
		{
			if(this.bindScript['src']!=undefined)
			{
				var scriptPath = this.bindScript['src'];
				var scriptName = (this.bindScript['name']!=undefined)
									? this.bindScript['name'] : '';
				this.IncludeFile(scriptName,scriptPath,this.bindScript['target_fn']);
			}
		}//if bindScript
		/* */
		
		this.isFirstLoad = false;
		this.hasGrid = true;
		
		if( this.rememberGridValues ||
			this.rememberPageLen ||
			this.rememberPageNb )
			this.ResetValues();
		
		this.ShowLoader('none');
		
		if(this.onFiltersLoaded)
			this.onFiltersLoaded.call(null,this);

		/* Loads extensions */
		this.LoadExtensions();
		/* */
	},// AddGrid
	
	EvtManager: function( evt,s )
	/*====================================================
		- TF events manager
		- Params: 
			- event name (string)
			- config object (optional literal object)
	=====================================================*/
	{
		var o = this;
		var slcIndex = (s!=undefined && s.slcIndex!=undefined) ? s.slcIndex : null;
		var slcExternal = (s!=undefined && s.slcExternal!=undefined) ? s.slcExternal : false;
		var slcId = (s!=undefined && s.slcId!=undefined) ? s.slcId : null;
		var pgIndex = (s!=undefined && s.pgIndex!=undefined) ? s.pgIndex : null;
		function efx(){
			if(evt!=undefined)
			switch( evt )
			{
				case o.Evt.name.filter:
					(o.isModFilterFn) 
						? o.modFilterFn.call(null,o)
						: o._Filter();
				break;
				case o.Evt.name.populateselect:
					(o.refreshFilters) 
						? o._PopulateSelect(slcIndex,true) 
						: o._PopulateSelect(slcIndex,false,slcExternal,slcId);
				break;
				case o.Evt.name.populatechecklist:
					o._PopulateCheckList(slcIndex,slcExternal,slcId);
				break;
				case o.Evt.name.changepage:
					o._ChangePage(pgIndex);
				break;
				case o.Evt.name.clear:
					o._ClearFilters(); 
					o._Filter();
				break;
				case o.Evt.name.changeresultsperpage:
					o._ChangeResultsPerPage();
				break;
				case o.Evt.name.resetvalues:
					o._ResetValues();					
					o._Filter();
				break;
				case o.Evt.name.resetpage:
					o._ResetPage(o.pgNbCookie);
				break;
				case o.Evt.name.resetpagelength:
					o._ResetPageLength(o.pgLenCookie);
				break;
				case o.Evt.name.sort:
					void(0);
				break;
				case o.Evt.name.loadextensions:
					o._LoadExtensions();
				break;
				default: //to be used by extensions events when needed
					o['_'+evt].call(null,o,s);
				break;
			}
			o.StatusMsg('');
			o.ShowLoader('none');
		}
		
		if(this.loader || this.status || this.statusBar)
		{
			this.ShowLoader('');
			this.StatusMsg(o['msg'+evt]);
			window.setTimeout(efx,this.execDelay);
		} else efx();
	},
	
	LoadExtensions: function()
	{
		this.EvtManager(this.Evt.name.loadextensions);
	},
	
	_LoadExtensions: function()
	/*====================================================
		- loads TF extensions
	=====================================================*/
	{
		if(!this.hasExtensions) return;
		if((typeof this.extensions.name).tf_LCase() == 'object' && 
				(typeof this.extensions.src).tf_LCase() == 'object')
		{
			var ext = this.extensions;
			for(var e=0; e<ext.name.length; e++)
			{
				var extPath = ext.src[e];
				var extName = ext.name[e];
				var extInit = (ext.initialize && ext.initialize[e]) ? ext.initialize[e] : null;
				var extDesc = (ext.description && ext.description[e] ) ? ext.description[e] : null;
				
				//Registers extension 
				this.Ext.add(extName, extDesc, extPath, extInit);
				
				if(tf_isImported(extPath) && extInit)
				{
					try{ extInit.call(null,this); }
					catch(e){
						var o = this;
						function fn(){extInit.call(null,o);}
						if(!tf_isIE) tf_addEvent(window,'load',fn); 
						else{
							function testReady(){
								if (document.readyState == "complete") 
								{
									fn(); clearInterval(s);
								}
							}
							var s = setInterval(testReady,10);
						}		
					}
				}
				else
					this.IncludeFile(extName,extPath,extInit);
			}
		}
	},
	
	RemoveGrid: function()
	/*====================================================
		- removes a filter grid
	=====================================================*/
	{
		if( this.fltGrid && this.hasGrid )
		{
			var row = this.tbl.rows;
			
			this.RemovePaging();
			this.RemoveStatusBar();
			this.RemoveRowsCounter();
			this.RemoveResetBtn();
			this.RemoveResultsPerPage();
			this.RemoveExternalFlts();
			this.RemoveFixedHeaders();
			this.RemoveTopDiv();
			this.UnhighlightAll();
			this.RemoveSort();
			this.RemoveLoader();
			
			for(var j=this.refRow; j<this.nbRows; j++)
			{//this loop shows all rows and removes validRow attribute			
				row[j].style.display = '';
				try
				{ 
					if( row[j].hasAttribute('validRow') ) 
						row[j].removeAttribute('validRow');
				} //ie<=6 doesn't support hasAttribute method
				catch(e){
					for( var x = 0; x < row[j].attributes.length; x++ ) 
					{
						if( row[j].attributes[x].nodeName.tf_LCase()=='validrow' ) 
							row[j].removeAttribute('validRow');
					}//for x
				}//catch(e)
				
				//removes alterning colors
				this.RemoveRowBg(j);
				
			}//for j
	
			if(this.fltGrid && !this.gridLayout)
			{
				this.fltGridEl = row[this.filtersRowIndex];			
				this.tbl.deleteRow(this.filtersRowIndex);
			}
			this.activeFlt = null;
			this.isStartBgAlternate = true;
			this.hasGrid = false;
			this.RemoveGridLayout();
	
		}//if this.fltGrid
	},
	
	SetGridLayout: function()
	/*====================================================
		- generates a grid with fixed headers
	=====================================================*/
	{
		if(!this.gridLayout) return;
		if(!this.hasColWidth){// in case column widths are not set default width 100px
			this.colWidth = [];
			for(var k=0; k<this.nbCells; k++){
				var colW, cell = this.tbl.rows[this.gridHeadRowIndex].cells[k];
				if(cell.width!='') colW = cell.width;
				else if(cell.style.width!='') colW = parseInt(cell.style.width);
				else colW = this.gridDefaultColWidth;
				this.colWidth[k] = colW;
			}
			this.hasColWidth = true;
		}
		this.SetColWidths(this.gridHeadRowIndex);
		
		var tblW;//initial table width
		if(this.tbl.width!='') tblW = this.tbl.width;
		else if(this.tbl.style.width!='') tblW = parseInt(this.tbl.style.width);
		else tblW = this.tbl.clientWidth;
		
		//Main container: it will contain all the elements
		this.tblMainCont = tf_CreateElm('div',['id', this.prfxMainTblCont + this.id]);
		this.tblMainCont.className = this.gridMainContCssClass;
		if(this.gridWidth) this.tblMainCont.style.width = this.gridWidth;
		this.tbl.parentNode.insertBefore(this.tblMainCont, this.tbl);
		
		//Table container: div wrapping content table
		this.tblCont = tf_CreateElm('div',['id', this.prfxTblCont + this.id]);
		this.tblCont.className = this.gridContCssClass;
		if(this.gridWidth) this.tblCont.style.width = this.gridWidth;
		if(this.gridHeight) this.tblCont.style.height = this.gridHeight;
		this.tbl.parentNode.insertBefore(this.tblCont, this.tbl);
		var t = this.tbl.parentNode.removeChild(this.tbl);
		this.tblCont.appendChild(t);
		
		//In case table width is expressed in %
		if(this.tbl.style.width == '')
			this.tbl.style.width = (this.__containsStr('%',tblW) 
									? this.tbl.clientWidth : tblW) + 'px';

		var d = this.tblCont.parentNode.removeChild(this.tblCont);
		this.tblMainCont.appendChild(d);
		
		//Headers table container: div wrapping headers table
		this.headTblCont = tf_CreateElm('div',['id', this.prfxHeadTblCont + this.id]);
		this.headTblCont.className = this.gridHeadContCssClass;
		if(this.gridWidth) this.headTblCont.style.width = this.gridWidth;		
		
		//Headers table
		this.headTbl = tf_CreateElm('table',['id', this.prfxHeadTbl + this.id]);
		var tH = tf_CreateElm('tHead'); //IE<7 needs it
		
		//1st row should be headers row, ids are added if not set
		//Those ids are used by the sort feature
		var hRow = this.tbl.rows[this.gridHeadRowIndex];
		var sortTriggers = [];
		for(var n=0; n<this.nbCells; n++){
			var cell = hRow.cells[n];
			var thId = cell.getAttribute('id');
			if(!thId || thId==''){ 
				thId = this.prfxGridTh+n+'_'+this.id 
				cell.setAttribute('id', thId);
			}
			sortTriggers.push(thId);
		}
		
		//Filters row is created
		var filtersRow = tf_CreateElm('tr');
		if(this.gridEnableFilters && this.fltGrid){
			this.externalFltTgtIds = [];
			for(var j=0; j<this.nbCells; j++)
			{
				var fltTdId = this.prfxFlt+j+ this.prfxGridFltTd +this.id;
				var c = tf_CreateElm(this.fltCellTag, ['id', fltTdId]);
				filtersRow.appendChild(c);
				this.externalFltTgtIds[j] = fltTdId;
			}
		} 
		//Headers row are moved from content table to headers table
		for(var i=0; i<this.gridHeadRows.length; i++)
		{
			var headRow = this.tbl.rows[this.gridHeadRows[0]];			
			tH.appendChild(headRow);
		}
		this.headTbl.appendChild(tH);
		if(this.filtersRowIndex == 0) tH.insertBefore(filtersRow,hRow);
		if(this.filtersRowIndex == 1) tH.appendChild(filtersRow);
		
		this.headTblCont.appendChild(this.headTbl);
		this.tblCont.parentNode.insertBefore(this.headTblCont, this.tblCont);
		
		//THead needs to be removed in content table for sort feature
		var thead = tf_Tag(this.tbl,'thead');
		if( thead.length>0 ) this.tbl.removeChild(thead[0]);

		//Headers table style
		this.headTbl.style.width = this.tbl.style.width;
		this.headTbl.style.tableLayout = 'fixed';
		this.tbl.style.tableLayout = 'fixed';
		this.headTbl.cellPadding = this.tbl.cellPadding;
		this.headTbl.cellSpacing = this.tbl.cellSpacing;
		
		//Headers container width
		this.headTblCont.style.width = this.tblCont.clientWidth+'px';
		
		//content table without headers needs col widths to be reset
		this.SetColWidths();
		
		this.tbl.style.width = '';		
		if(tf_isIE || tf_isIE7)	this.headTbl.style.width = '';
		
		//scroll synchronisation
		var o = this; //TF object
		this.tblCont.onscroll = function(){
			o.headTblCont.scrollLeft = this.scrollLeft;
			var _o = this; //this = scroll element
			//New pointerX calc taking into account scrollLeft
			if(!o.isPointerXOverwritten){
				try{					
					TF.Evt.pointerX = function(e)
					{
						e = e || window.event;
						var scrollLeft = tf_StandardBody().scrollLeft + _o.scrollLeft;
						return (e.pageX + _o.scrollLeft) || (e.clientX + scrollLeft);
					}					
					o.isPointerXOverwritten = true;
				} catch(ee) {
					o.isPointerXOverwritten = false;
				}
			}
		}

		/*** Default behaviours activation ***/
		var f = this.fObj==undefined ? {} : this.fObj;
		
		//Sort is enabled if not specified in config object
		if(f.sort != false){
			this.sort = true;
			this.sortConfig.asyncSort = true;
			this.sortConfig.triggerIds = sortTriggers;
		}
		
		if(this.gridEnableColResizer){
			if(!this.hasExtensions){
				this.extensions = {
					name:['ColumnsResizer'],
					src:['TFExt_ColsResizer/TFExt_ColsResizer.js'], 
					description:['Columns Resizing'],
					initialize:[function(o){o.SetColsResizer('ColumnsResizer');}]
				}
				this.hasExtensions = true;
			} else {
				if(!this.__containsStr('colsresizer',this.extensions.src.toString().tf_LCase())){
					this.extensions.name.push('ColumnsResizer');
					this.extensions.src.push('TFExt_ColsResizer/TFExt_ColsResizer.js');
					this.extensions.description.push('Columns Resizing');
					this.extensions.initialize.push(function(o){o.SetColsResizer('ColumnsResizer');});
				}  
			}
		}
		
		//Default columns resizer properties for grid layout
		f.col_resizer_cols_headers_table = this.headTbl.getAttribute('id');
		f.col_resizer_cols_headers_index = this.gridHeadRowIndex;
		f.col_resizer_width_adjustment = 0;
		f.col_enable_text_ellipsis = false;
		
		//Cols generation for all browsers excepted IE<=7
		o.tblHasColTag = (tf_Tag(o.tbl,'col').length > 0) ? true : false;
		if(!tf_isIE && !tf_isIE7){
			//Col elements are enough to keep column widths after sorting and filtering
			function createColTags(o)
			{
				if(!o) return;
				for(var k=(o.nbCells-1); k>=0; k--)
				{
					var col = tf_CreateElm( 'col', ['id', o.id+'_col_'+k]);
					o.tbl.firstChild.parentNode.insertBefore(col,o.tbl.firstChild);
					col.style.width = o.colWidth[k];
					o.gridColElms[k] = col;
				}
				o.tblHasColTag = true;
			}
			if(!o.tblHasColTag) createColTags(o);
			else{
				var cols = tf_Tag(o.tbl,'col');
				for(var i=0; i<o.nbCells; i++){
					cols[i].setAttribute('id', o.id+'_col_'+i);
					cols[i].style.width = o.colWidth[i];
					o.gridColElms.push(cols[i]);
				}
			}
		}
		
		//IE <= 7 needs an additional row for widths as col element width is not enough...
		if(tf_isIE || tf_isIE7){
			var tbody = tf_Tag(o.tbl,'tbody'), r;
			if( tbody.length>0 ) r = tbody[0].insertRow(0);
			else r = o.tbl.insertRow(0);
			r.style.height = '0px';
			for(var i=0; i<o.nbCells; i++){
				var col = tf_CreateElm('td', ['id', o.id+'_col_'+i]);
				col.style.width = o.colWidth[i];
				o.tbl.rows[1].cells[i].style.width = '';
				r.appendChild(col);
				o.gridColElms.push(col);
			}
			this.hasGridWidthsRow = true;
			//Data table row with widths expressed
			o.leadColWidthsRow = o.tbl.rows[0];
			o.leadColWidthsRow.setAttribute('validRow','false');
			
			var beforeSortFn = tf_isFn(f.on_before_sort) ? f.on_before_sort : null;
			f.on_before_sort = function(o,colIndex){
				o.leadColWidthsRow.setAttribute('validRow','false');
				if(beforeSortFn!=null) beforeSortFn.call(null,o,colIndex);
			} 
			
			var afterSortFn = tf_isFn(f.on_after_sort) ? f.on_after_sort : null;
			f.on_after_sort = function(o,colIndex){
				if(o.leadColWidthsRow.rowIndex != 0){
					var r = o.leadColWidthsRow;
					if( tbody.length>0 )
						tbody[0].moveRow(o.leadColWidthsRow.rowIndex, 0);
					else o.tbl.moveRow(o.leadColWidthsRow.rowIndex, 0);
				}
				if(afterSortFn!=null) afterSortFn.call(null,o,colIndex);
			}	
		}
		
		var afterColResizedFn = tf_isFn(f.on_after_col_resized) ? f.on_after_col_resized : null;
		f.on_after_col_resized = function(o,colIndex){
			if(colIndex==undefined) return;
			var w = o.crWColsRow.cells[colIndex].style.width;
			var col = o.gridColElms[colIndex];
			col.style.width = w;
			
			var thCW = o.crWColsRow.cells[colIndex].clientWidth;
			var tdCW = o.crWRowDataTbl.cells[colIndex].clientWidth;
			
			if(tf_isIE || tf_isIE7)
				o.tbl.style.width = o.headTbl.clientWidth+'px';
			
			if(thCW != tdCW && !tf_isIE && !tf_isIE7)
				o.headTbl.style.width = o.tbl.clientWidth+'px'; 
			
			if(afterColResizedFn!=null) afterColResizedFn.call(null,o,colIndex);			
		}	
		
		if(this.tbl.clientWidth != this.headTbl.clientWidth)
			this.tbl.style.width = this.headTbl.clientWidth+'px';
	},
	
	RemoveGridLayout: function()
	{
		if(!this.gridLayout) return;		
		var t = this.tbl.parentNode.removeChild(this.tbl);
		this.tblMainCont.parentNode.insertBefore(t, this.tblMainCont);
		this.tblMainCont.parentNode.removeChild( this.tblMainCont );
		this.tblMainCont = null;
		this.headTblCont = null;
		this.headTbl = null;
		this.tblCont = null;
		//TO DO: alternative solution for Firefox
		this.tbl.outerHTML = this.sourceTblHtml;
		this.tbl = tf_Id(this.id);
		this.isFirstLoad = true;
		this.activeFlt = null;
		this.isStartBgAlternate = true;
		this.hasGrid = false;
	},
	
	SetTopDiv: function()
	/*====================================================
		- Generates div above table where paging,
		reset button, rows counter label etc. are placed
	=====================================================*/
	{
		if( this.infDiv!=null ) return;
	
		/*** container div ***/
		var infdiv = tf_CreateElm( 'div',['id',this.prfxInfDiv+this.id] );
		infdiv.className = this.infDivCssClass;// setAttribute method doesn't seem to work on ie<=6
		if(this.fixedHeaders && this.contDiv)
			this.contDiv.parentNode.insertBefore(infdiv, this.contDiv);
		else if(this.gridLayout){
			this.tblMainCont.appendChild(infdiv);
			infdiv.className = this.gridInfDivCssClass;
		}
		else
			this.tbl.parentNode.insertBefore(infdiv, this.tbl);
		this.infDiv = tf_Id( this.prfxInfDiv+this.id );
		
		/*** left div containing rows # displayer ***/
		var ldiv = tf_CreateElm( 'div',['id',this.prfxLDiv+this.id] );
		ldiv.className = this.lDivCssClass;/*'ldiv'*/;
		infdiv.appendChild(ldiv);
		this.lDiv = tf_Id( this.prfxLDiv+this.id );		
		
		/*** 	right div containing reset button 
				+ nb results per page select 	***/	
		var rdiv = tf_CreateElm( 'div',['id',this.prfxRDiv+this.id] );
		rdiv.className = this.rDivCssClass/*'rdiv'*/;
		infdiv.appendChild(rdiv);
		this.rDiv = tf_Id( this.prfxRDiv+this.id );
		
		/*** mid div containing paging elements ***/
		var mdiv = tf_CreateElm( 'div',['id',this.prfxMDiv+this.id] );
		mdiv.className = this.mDivCssClass/*'mdiv'*/;						
		infdiv.appendChild(mdiv);
		this.mDiv = tf_Id( this.prfxMDiv+this.id );
	},
	
	RemoveTopDiv: function()
	/*====================================================
		- Removes div above table where paging,
		reset button, rows counter label etc. are placed
	=====================================================*/
	{
		if( this.infDiv==null ) return;
		this.infDiv.parentNode.removeChild( this.infDiv );
		this.infDiv = null;
	},
	
	SetFixedHeaders: function()
	/*====================================================
		- CSS solution making headers fixed
	=====================================================*/
	{
		if((!this.hasGrid && !this.isFirstLoad) || !this.fixedHeaders) return;
		if(this.contDiv) return;	
		var thead = tf_Tag(this.tbl,'thead');
		if( thead.length==0 ) return;
		var tbody = tf_Tag(this.tbl,'tbody');	
		if( tbody[0].clientHeight!=0 ) 
		{//firefox returns tbody height
			//previous values
			this.prevTBodyH = tbody[0].clientHeight;
			this.prevTBodyOverflow = tbody[0].style.overflow;
			this.prevTBodyOverflowX = tbody[0].style.overflowX;
			
			tbody[0].style.height = this.tBodyH+'px';
			tbody[0].style.overflow = 'auto';
			tbody[0].style.overflowX = 'hidden';
		} else { //IE returns 0
			// cont div is added to emulate fixed headers behaviour
			var contDiv = tf_CreateElm( 'div',['id',this.prfxContentDiv+this.id] );
			contDiv.className = this.contDivCssClass;
			this.tbl.parentNode.insertBefore(contDiv, this.tbl);
			contDiv.appendChild(this.tbl);
			this.contDiv = tf_Id(this.prfxContentDiv+this.id);
			//prevents headers moving during window scroll (IE)
			this.contDiv.style.position = 'relative';
			
			var theadH = 0;
			var theadTr = tf_Tag(thead[0],'tr');	
			for(var i=0; i<theadTr.length; i++)
			{//css below emulates fixed headers on IE<=6
				theadTr[i].style.cssText += 'position:relative; ' +
											'top:expression(offsetParent.scrollTop);';
				theadH += parseInt(theadTr[i].clientHeight);
			}
			
			this.contDiv.style.height = (this.tBodyH+theadH)+'px';
			
			var tfoot = tf_Tag(this.tbl,'tfoot');
			if( tfoot.length==0 ) return;
			
			var tfootTr = tf_Tag(tfoot[0],'tr');
				
			for(var j=0; j<tfootTr.length; j++)//css below emulates fixed footer on IE<=6
				tfootTr[j].style.cssText += 'position:relative; overflow-x: hidden; ' +
											'top: expression(parentNode.parentNode.offsetHeight >= ' +
											'offsetParent.offsetHeight ? 0 - parentNode.parentNode.offsetHeight + '+ 
											'offsetParent.offsetHeight + offsetParent.scrollTop : 0);';		
		}	
	},
	
	RemoveFixedHeaders: function()
	/*====================================================
		- Removes fixed headers
	=====================================================*/
	{
		if(!this.hasGrid || !this.fixedHeaders ) return;
		if( this.contDiv )//IE additional div
		{
			this.contDiv.parentNode.insertBefore(this.tbl, this.contDiv);
			this.contDiv.parentNode.removeChild( this.contDiv );
			this.contDiv = null;
			var thead = tf_Tag(this.tbl,'thead');
			if( thead.length==0 ) return;
			var theadTr = tf_Tag(thead[0],'tr');
			if( theadTr.length==0 ) return;
			for(var i=0; i<theadTr.length; i++)
				theadTr[i].style.cssText = '';
			var tfoot = tf_Tag(this.tbl,'tfoot');
			if( tfoot.length==0 ) return;		
			var tfootTr = tf_Tag(tfoot[0],'tr');	
			for(var j=0; j<tfootTr.length; j++)
			{
				tfootTr[j].style.position = 'relative';
				tfootTr[j].style.top = '';
				tfootTr[j].style.overeflowX = '';
			}
		} else {
			var tbody = tf_Tag(this.tbl,'tbody');
			if( tbody.length==0 ) return;
			tbody[0].style.height = this.prevTBodyH+'px';
			tbody[0].style.overflow = this.prevTBodyOverflow;
			tbody[0].style.overflowX = this.prevTBodyOverflowX;
		}
	},
	
	SetPaging: function()
	/*====================================================
		- Generates paging elements:
			- pages drop-down list
			- previous, next, first, last buttons
	=====================================================*/
	{
		if(!this.hasGrid && !this.isFirstLoad) return;
		if(!this.paging || (!this.isPagingRemoved && !this.isFirstLoad)) return;
		var start_row = this.refRow;
		var nrows = this.nbRows;
		this.nbPages = Math.ceil( (nrows-start_row)/this.pagingLength );//calculates page nb
	
		// Paging drop-down list selector
		if(this.pageSelectorType == this.fltTypeSlc)
		{
			var slcPages = tf_CreateElm( this.fltTypeSlc, ['id',this.prfxSlcPages+this.id] );
			slcPages.className = this.pgSlcCssClass;
			slcPages.onchange = this.Evt._OnSlcPagesChange;
		}
		// Paging input selector
		if(this.pageSelectorType == this.fltTypeInp)
		{
			var slcPages = tf_CreateElm( 
				this.fltTypeInp, 
				['id',this.prfxSlcPages+this.id],
				['value',this.currentPageNb]
			);
			slcPages.className = this.pgInpCssClass;
			slcPages.onkeypress = this.Evt._Paging._detectKey;
		}
		
		var btnNextSpan, btnPrevSpan, btnLastSpan, btnFirstSpan;// btns containers
		btnNextSpan = tf_CreateElm('span',['id',this.prfxBtnNextSpan+this.id]);
		btnPrevSpan = tf_CreateElm('span',['id',this.prfxBtnPrevSpan+this.id]);
		btnLastSpan = tf_CreateElm('span',['id',this.prfxBtnLastSpan+this.id]);
		btnFirstSpan = tf_CreateElm('span',['id',this.prfxBtnFirstSpan+this.id]);
		
		if(this.hasPagingBtns)
		{
			if(this.btnNextPageHtml==null)
			{// Next button
				var btn_next = tf_CreateElm( this.fltTypeInp,['id',this.prfxBtnNext+this.id],
					['type','button'],['value',this.btnNextPageText],['title','Next'] );
				btn_next.className = this.btnPageCssClass;
				btn_next.onclick = this.Evt._Paging.next;
				btnNextSpan.appendChild(btn_next);
			} else {
				btnNextSpan.innerHTML = this.btnNextPageHtml;
				btnNextSpan.onclick = this.Evt._Paging.next;
			}
			
			if(this.btnPrevPageHtml==null)
			{// Previous button
				var btn_prev = tf_CreateElm( this.fltTypeInp,['id',this.prfxBtnPrev+this.id],
					['type','button'],['value',this.btnPrevPageText],['title','Previous'] );
				btn_prev.className = this.btnPageCssClass;
				btn_prev.onclick = this.Evt._Paging.prev;
				btnPrevSpan.appendChild(btn_prev);
			} else { 
				btnPrevSpan.innerHTML = this.btnPrevPageHtml;
				btnPrevSpan.onclick = this.Evt._Paging.prev;
			}
			
			if(this.btnLastPageHtml==null)
			{// Last button
				var btn_last = tf_CreateElm( this.fltTypeInp,['id',this.prfxBtnLast+this.id],
					['type','button'],['value',this.btnLastPageText],['title','Last'] );
				btn_last.className = this.btnPageCssClass;
				btn_last.onclick = this.Evt._Paging.last;
				btnLastSpan.appendChild(btn_last);
			} else { 
				btnLastSpan.innerHTML = this.btnLastPageHtml;
				btnLastSpan.onclick = this.Evt._Paging.last;
			}
			
			if(this.btnFirstPageHtml==null)
			{// First button
				var btn_first = tf_CreateElm( this.fltTypeInp,['id',this.prfxBtnFirst+this.id],
					['type','button'],['value',this.btnFirstPageText],['title','First'] );
				btn_first.className = this.btnPageCssClass;
				btn_first.onclick = this.Evt._Paging.first;
				btnFirstSpan.appendChild(btn_first);
			} else { 
				btnFirstSpan.innerHTML = this.btnFirstPageHtml;
				btnFirstSpan.onclick = this.Evt._Paging.first;
			}			
		}//if this.hasPagingBtns
		
		// paging elements (buttons+drop-down list) are added to defined element
		if(this.pagingTgtId==null) this.SetTopDiv();
		var targetEl = ( this.pagingTgtId==null ) ? this.mDiv : tf_Id( this.pagingTgtId );
		
		/***	if paging previously removed this prevents IE memory leak with removeChild 
				used in RemovePaging method. For more info refer to
				http://forums.microsoft.com/MSDN/ShowPost.aspx?PostID=2840253&SiteID=1	***/
		if ( targetEl.innerHTML!='' ) targetEl.innerHTML = '';
		/*** ***/
		
		targetEl.appendChild(btnPrevSpan);
		targetEl.appendChild(btnFirstSpan);
		
		var pgBeforeSpan = tf_CreateElm( 'span',['id',this.prfxPgBeforeSpan+this.id] );
		pgBeforeSpan.appendChild( tf_CreateText(' Page ') );
		pgBeforeSpan.className = this.nbPgSpanCssClass;
		targetEl.appendChild(pgBeforeSpan);
		targetEl.appendChild(slcPages);
		var pgAfterSpan = tf_CreateElm( 'span',['id',this.prfxPgAfterSpan+this.id] );
		pgAfterSpan.appendChild( tf_CreateText(' of ') );
		pgAfterSpan.className = this.nbPgSpanCssClass;
		targetEl.appendChild(pgAfterSpan)
		var pgspan = tf_CreateElm( 'span',['id',this.prfxPgSpan+this.id] );
		pgspan.className = this.nbPgSpanCssClass;
		pgspan.appendChild( tf_CreateText(' '+this.nbPages+' ') );
		targetEl.appendChild(pgspan);
		targetEl.appendChild(btnLastSpan);
		targetEl.appendChild(btnNextSpan);
	
		this.pagingSlc = tf_Id(this.prfxSlcPages+this.id); //to be easily re-used
		
		// if this.rememberGridValues==true this.SetPagingInfo() is called
		// in ResetGridValues() method
		if( !this.rememberGridValues || this.isPagingRemoved )
			this.SetPagingInfo();
		if( !this.fltGrid )
		{
			this.ValidateAllRows();
			this.SetPagingInfo(this.validRowsIndex);
		}
			
		this.pagingBtnEvents = this.Evt._Paging;
		this.isPagingRemoved = false;
	},
	
	RemovePaging: function()
	/*====================================================
		- Removes paging elements
	=====================================================*/
	{
		if(!this.hasGrid) return;
		if( this.pagingSlc==null ) return;
		var btnNextSpan, btnPrevSpan, btnLastSpan, btnFirstSpan;// btns containers
		var pgBeforeSpan, pgAfterSpan, pgspan;
		btnNextSpan = tf_Id(this.prfxBtnNextSpan+this.id);
		btnPrevSpan = tf_Id(this.prfxBtnPrevSpan+this.id);
		btnLastSpan = tf_Id(this.prfxBtnLastSpan+this.id);
		btnFirstSpan = tf_Id(this.prfxBtnFirstSpan+this.id);
		pgBeforeSpan = tf_Id(this.prfxPgBeforeSpan+this.id);//span containing 'Page' text
		pgAfterSpan = tf_Id(this.prfxPgAfterSpan+this.id);//span containing 'of' text
		pgspan = tf_Id(this.prfxPgSpan+this.id);//span containing nb of pages
		
		this.pagingSlc.parentNode.removeChild(this.pagingSlc);
		
		if( btnNextSpan!=null )
			btnNextSpan.parentNode.removeChild( btnNextSpan );
	
		if( btnPrevSpan!=null )
			btnPrevSpan.parentNode.removeChild( btnPrevSpan );
	
		if( btnLastSpan!=null )
			btnLastSpan.parentNode.removeChild( btnLastSpan );
	
		if( btnFirstSpan!=null )
			btnFirstSpan.parentNode.removeChild( btnFirstSpan );
	
		if( pgBeforeSpan!=null )
			pgBeforeSpan.parentNode.removeChild( pgBeforeSpan );
	
		if( pgAfterSpan!=null )
			pgAfterSpan.parentNode.removeChild( pgAfterSpan );
	
		if( pgspan!=null )
			pgspan.parentNode.removeChild( pgspan );
		
		this.pagingBtnEvents = null;	
		this.pagingSlc = null;
		this.isPagingRemoved = true;
	},
	
	SetRowsCounter: function()
	/*====================================================
		- Generates rows counter label
	=====================================================*/
	{
		if(!this.hasGrid && !this.isFirstLoad) return;
		if( this.rowsCounterSpan!=null ) return;
		var countDiv = tf_CreateElm( 'div',['id',this.prfxCounter+this.id] ); //rows counter container
		countDiv.className = this.totRowsCssClass;
		var countSpan = tf_CreateElm( 'span',['id',this.prfxTotRows+this.id] ); //rows counter label
		var countText = tf_CreateElm( 'span',['id',this.prfxTotRowsTxt+this.id] );
		countText.appendChild( tf_CreateText(this.rowsCounterText) );
		
		// counter is added to defined element
		if(this.rowsCounterTgtId==null) this.SetTopDiv();
		var targetEl = ( this.rowsCounterTgtId==null ) ? this.lDiv : tf_Id( this.rowsCounterTgtId );
		
		//IE only: clears all for sure
		if(this.rowsCounterDiv && tf_isIE)
			this.rowsCounterDiv.outerHTML = '';
		
		if( this.rowsCounterTgtId==null )
		{//default container: 'lDiv'
			countDiv.appendChild(countText);
			countDiv.appendChild(countSpan);
			targetEl.appendChild(countDiv);
		}
		else
		{// custom container, no need to append statusDiv
			targetEl.appendChild(countText);
			targetEl.appendChild(countSpan);
		}
		this.rowsCounterDiv = tf_Id( this.prfxCounter+this.id );
		this.rowsCounterSpan = tf_Id( this.prfxTotRows+this.id );
		
		this.RefreshNbRows();	
	},
	
	RemoveRowsCounter: function()
	/*====================================================
		- Removes rows counter label
	=====================================================*/
	{
		if(!this.hasGrid) return;
		if( this.rowsCounterSpan==null ) return;
		
		if(this.rowsCounterTgtId==null && this.rowsCounterDiv)
		{
			//IE only: clears all for sure
			if(tf_isIE) this.rowsCounterDiv.outerHTML = '';
			else
				this.rowsCounterDiv.parentNode.removeChild( 
					this.rowsCounterDiv
				);
		} else {
			tf_Id( this.rowsCounterTgtId ).innerHTML = '';
		}
		this.rowsCounterSpan = null;
		this.rowsCounterDiv = null;
	},
	
	SetStatusBar: function()
	/*====================================================
		- Generates status bar label
	=====================================================*/
	{
		if(!this.hasGrid && !this.isFirstLoad) return;
		var statusDiv = tf_CreateElm( 'div',['id',this.prfxStatus+this.id] ); //status bar container
		statusDiv.className = this.statusBarCssClass;
		var statusSpan = tf_CreateElm( 'span',['id',this.prfxStatusSpan+this.id] ); //status bar label
		var statusSpanText = tf_CreateElm( 'span',['id',this.prfxStatusTxt+this.id] );//preceding text
		statusSpanText.appendChild( tf_CreateText(this.statusBarText) );
	
		// target element container
		if(this.statusBarTgtId==null) this.SetTopDiv();
		var targetEl = ( this.statusBarTgtId==null ) ? this.lDiv : tf_Id( this.statusBarTgtId );
		
		if(this.statusBarDiv && tf_isIE)
			this.statusBarDiv.outerHTML = '';
		
		if( this.statusBarTgtId==null )
		{//default container: 'lDiv'
			statusDiv.appendChild(statusSpanText);
			statusDiv.appendChild(statusSpan);
			targetEl.appendChild(statusDiv);
		}
		else
		{// custom container, no need to append statusDiv
			targetEl.appendChild(statusSpanText);
			targetEl.appendChild(statusSpan);
		}

		this.statusBarDiv = tf_Id( this.prfxStatus+this.id );
		this.statusBarSpan = tf_Id( this.prfxStatusSpan+this.id );
		this.statusBarSpanText = tf_Id( this.prfxStatusTxt+this.id );
	},
	
	RemoveStatusBar: function()
	/*====================================================
		- Removes status bar div
	=====================================================*/
	{
		if(!this.hasGrid) return;
		if(this.statusBarDiv)
		{
			this.statusBarDiv.innerHTML = '';
			this.statusBarDiv.parentNode.removeChild( 
				this.statusBarDiv
			);
			this.statusBarSpan = null;
			this.statusBarSpanText = null;
			this.statusBarDiv = null;
		}
	},
	
	SetResultsPerPage: function()
	/*====================================================
		- Generates results per page select + label
	=====================================================*/
	{
		if(!this.hasGrid && !this.isFirstLoad) return;
		if( this.resultsPerPageSlc!=null || this.resultsPerPage==null ) return;
		var slcR = tf_CreateElm( this.fltTypeSlc,['id',this.prfxSlcResults+this.id] );
		slcR.className = this.resultsSlcCssClass;
		var slcRText = this.resultsPerPage[0], slcROpts = this.resultsPerPage[1];
		var slcRSpan = tf_CreateElm( 'span',['id',this.prfxSlcResultsTxt+this.id] );
		slcRSpan.className = this.resultsSpanCssClass;
		
		// results per page select is added to defined element
		if(this.resultsPerPageTgtId==null) this.SetTopDiv();
		var targetEl = ( this.resultsPerPageTgtId==null ) ? this.rDiv : tf_Id( this.resultsPerPageTgtId );
		slcRSpan.appendChild(tf_CreateText(slcRText));
		targetEl.appendChild(slcRSpan);
		targetEl.appendChild(slcR);
		
		this.resultsPerPageSlc = tf_Id(this.prfxSlcResults+this.id);
		
		for(var r=0; r<slcROpts.length; r++)
		{
			var currOpt = new Option(slcROpts[r],slcROpts[r],false,false);
			this.resultsPerPageSlc.options[r] = currOpt;
		}
		slcR.onchange = this.Evt._OnSlcResultsChange;
	},
	
	RemoveResultsPerPage: function()
	/*====================================================
		- Removes results per page select + label
	=====================================================*/
	{
		if(!this.hasGrid) return;
		if( this.resultsPerPageSlc==null || this.resultsPerPage==null ) return;
		var slcR, slcRSpan;
		slcR = this.resultsPerPageSlc;
		slcRSpan = tf_Id( this.prfxSlcResultsTxt+this.id );
		if( slcR!=null )
			slcR.parentNode.removeChild( slcR );
		if( slcRSpan!=null )
			slcRSpan.parentNode.removeChild( slcRSpan );
		this.resultsPerPageSlc = null;
	},
	
	SetResetBtn: function()
	/*====================================================
		- Generates reset button
	=====================================================*/
	{
		if(!this.hasGrid && !this.isFirstLoad) return;
		if( this.btnResetEl!=null ) return;
		var resetspan = tf_CreateElm('span',['id',this.prfxResetSpan+this.id]);
		
		// reset button is added to defined element
		if(this.btnResetTgtId==null) this.SetTopDiv();
		var targetEl = ( this.btnResetTgtId==null ) ? this.rDiv : tf_Id( this.btnResetTgtId );
		targetEl.appendChild(resetspan);
			
		if(this.btnResetHtml==null)
		{	
			var fltreset = tf_CreateElm( 'a', ['href','javascript:void(0);'] );
			fltreset.className = this.btnResetCssClass;
			fltreset.appendChild(tf_CreateText(this.btnResetText));
			resetspan.appendChild(fltreset);
			fltreset.onclick = this.Evt._Clear;
		} else {
			resetspan.innerHTML = this.btnResetHtml;
			var resetEl = resetspan.firstChild;
			resetEl.onclick = this.Evt._Clear;
		}
		this.btnResetEl = tf_Id(this.prfxResetSpan+this.id).firstChild;	
	},
	
	RemoveResetBtn: function()
	/*====================================================
		- Removes reset button
	=====================================================*/
	{
		if(!this.hasGrid) return;
		if( this.btnResetEl==null ) return;
		var resetspan = tf_Id(this.prfxResetSpan+this.id);
		if( resetspan!=null )
			resetspan.parentNode.removeChild( resetspan );
		this.btnResetEl = null;	
	},
	
	RemoveExternalFlts: function()
	/*====================================================
		- removes external filters
	=====================================================*/
	{
		if( !this.isExternalFlt && !this.externalFltTgtIds ) return;
		for(var ct=0; ct<this.externalFltTgtIds.length; ct++ )
			if( tf_Id(this.externalFltTgtIds[ct]) )
				tf_Id(this.externalFltTgtIds[ct]).innerHTML = '';
	},
	
	SetSort: function()
	/*====================================================
		- Sets sorting feature by loading 
		WebFX Sortable Table 1.12 by Erik Arvidsson
		and TF adapter by Max Guglielmi
	=====================================================*/
	{
		if(tf_isImported(this.sortConfig.src))
			this.Evt._EnableSort();
		else
			this.IncludeFile(
				this.sortConfig.name, 
				this.sortConfig.src, 
				this.Evt._EnableSort
			);
	},
	
	RemoveSort: function()
	/*====================================================
		- removes sorting feature
	=====================================================*/
	{
		if(!this.sort) return;
		this.sort = false;
	},
	
	PopulateSelect: function(colIndex,isExternal,extSlcId)
	{ 
		this.EvtManager(
			this.Evt.name.populateselect,
			{ slcIndex:colIndex, slcExternal:isExternal, slcId:extSlcId }
		); 
	},
	_PopulateSelect: function(colIndex,isRefreshed,isExternal,extSlcId)
	/*====================================================
		- populates drop-down filters
	=====================================================*/
	{
		isExternal = (isExternal==undefined) ? false : isExternal;
		var slcId = this.fltIds[colIndex];
		if( tf_Id(slcId)==null && !isExternal ) return;
		if( tf_Id(extSlcId)==null && isExternal ) return;
		var slc = (!isExternal) ? tf_Id(slcId) : tf_Id(extSlcId);
		var o = this, row = this.tbl.rows;
		var fillMethod = this.slcFillingMethod.tf_LCase();
		var optArray = [], slcInnerHtml = '', opt0;
		var isCustomSlc = (this.hasCustomSlcOptions  //custom select test
							&& this.customSlcOptions.cols.tf_Has(colIndex));
		var optTxt = []; //custom selects text
		var activeFlt;
		if(isRefreshed && this.activeFilterId){
			activeFlt = this.activeFilterId.split('_')[0];
			activeFlt = activeFlt.split(this.prfxFlt)[1];
		}

		/*** remember grid values ***/
		var flts_values = [], fltArr = [];
		if(this.rememberGridValues)
		{
			flts_values = tf_CookieValueArray(this.fltsValuesCookie);			
			fltArr = (flts_values[colIndex]!=undefined) 
						? flts_values[colIndex].split(' '+this.orOperator+' ') 
						: flts_values[colIndex] = [];			
		}

		for(var k=this.refRow; k<this.nbRows; k++)
		{
			// always visible rows don't need to appear on selects as always valid
			if( this.hasVisibleRows && this.visibleRows.tf_Has(k) && !this.paging ) 
				continue;

			var cell = tf_Tag(row[k],'td');
			var nchilds = cell.length;

			if(nchilds == this.nbCells && !isCustomSlc)
			{// checks if row has exact cell #
				for(var j=0; j<nchilds; j++)// this loop retrieves cell data
				{
					if((colIndex==j && !isRefreshed) || 
						(colIndex==j && isRefreshed && ((row[k].style.display == '' && !this.paging) || 
						( this.paging && (!this.validRowsIndex || (this.validRowsIndex && this.validRowsIndex.tf_Has(k)))
							&& ((activeFlt==undefined || activeFlt==colIndex)  || (activeFlt!=colIndex && this.validRowsIndex.tf_Has(k) ))) )))
					{
						var cell_data = this.GetCellData(j, cell[j]);
						var cell_string = cell_data.tf_MatchCase(this.matchCase);//Váry Péter's patch
						// checks if celldata is already in array
						var isMatched = false;
						isMatched = optArray.tf_Has(cell_string,this.matchCase);
						
						if(!isMatched)
							optArray.push(cell_data);						
					}//if colIndex==j
				}//for j
			}//if
		}//for k
		
		//Retrieves custom values
		if(isCustomSlc)
		{
			var customValues = this.__getCustomValues(colIndex);
			optArray = customValues[0];
			optTxt = customValues[1];
		}
		
		if(this.sortSlc && !isCustomSlc)
			optArray.sort(this.matchCase ? null : tf_IgnoreCaseSort);
		
		if(this.sortNumAsc && this.sortNumAsc.tf_Has(colIndex))
		{//asc sort
			try{
				optArray.sort( tf_NumSortAsc ); 
				if(isCustomSlc) optTxt.sort( tf_NumSortAsc );
			} catch(e) {
				optArray.sort(); 
				if(isCustomSlc) optTxt.sort();
			}//in case there are alphanumeric values
		}
		if(this.sortNumDesc && this.sortNumDesc.tf_Has(colIndex))
		{//desc sort
			try{
				optArray.sort( tf_NumSortDesc ); 
				if(isCustomSlc) optTxt.sort( tf_NumSortDesc );
			} catch(e) {
				optArray.sort(); 
				if(isCustomSlc) optTxt.sort();
			}//in case there are alphanumeric values
		}
		
		AddOpts();//populates drop-down
		
		function AddOpt0()
		{// adds 1st option
			if( fillMethod == 'innerhtml' )
				slcInnerHtml += '<option value="">'+o.displayAllText+'</option>';
			else {
				var opt0 = tf_CreateOpt(o.displayAllText,'');			
				slc.appendChild(opt0);
			}
		}
		
		function AddOpts()
		{// populates select
			var slcValue = slc.value;
			slc.innerHTML = '';
			AddOpt0();			
			
			for(var y=0; y<optArray.length; y++)
			{			
				if( fillMethod == 'innerhtml' )
				{
					var slcAttr = '';
					var slcCustomTxt = (isCustomSlc) ? optTxt[y] : optArray[y];
					if( o.fillSlcOnDemand && slcValue==optArray[y] )
						slcAttr = 'selected="selected"';
					slcInnerHtml += '<option value="'+optArray[y]+'" '
										+slcAttr+'>'+slcCustomTxt+'</option>';
				} else {
					var opt;
					//fill select on demand
					if(o.fillSlcOnDemand && slcValue==optArray[y] && o['col'+colIndex]==o.fltTypeSlc)
						opt = tf_CreateOpt( (isCustomSlc) ? optTxt[y] : optArray[y],
											optArray[y],
											true );
					else{
						if( o['col'+colIndex]!=o.fltTypeMulti )
							opt = tf_CreateOpt( (isCustomSlc) ? optTxt[y] : optArray[y],
												optArray[y],
												(flts_values[colIndex]!=' ' && optArray[y]==flts_values[colIndex]) 
												? true : false 	);
						else
						{
							opt = tf_CreateOpt( (isCustomSlc) ? optTxt[y] : optArray[y],
												optArray[y],
												(fltArr.tf_Has(optArray[y].tf_MatchCase(o.matchCase),o.matchCase)) 
												? true : false 	);
						}
					}
					slc.appendChild(opt);
				}
			}// for y

			if( fillMethod == 'innerhtml' )
				slc.innerHTML += slcInnerHtml;
				
			slc.setAttribute('filled','1');
		}// fn AddOpt
	},
	
	PopulateCheckList: function(colIndex, isExternal, extFltId)
	{
		this.EvtManager(
			this.Evt.name.populatechecklist,
			{ slcIndex:colIndex, slcExternal:isExternal, slcId:extFltId }
		); 
	},
	_PopulateCheckList: function(colIndex, isExternal, extFltId)
	/*====================================================
		- populates checklist filters
	=====================================================*/
	{
		isExternal = (isExternal==undefined) ? false : isExternal;
		var divFltId = this.prfxCheckListDiv+colIndex+'_'+this.id;
		if( tf_Id(divFltId)==null && !isExternal ) return;
		if( tf_Id(extFltId)==null && isExternal ) return;
		var flt = (!isExternal) ? this.checkListDiv[colIndex] : tf_Id(extFltId);
		var ul = tf_CreateElm('ul',['id',this.fltIds[colIndex]],['colIndex',colIndex]);
		ul.className = this.checkListCssClass;
		ul.onchange = this.Evt._OnSlcChange;
		var o = this, row = this.tbl.rows;
		var optArray = [];
		var isCustomSlc = (this.hasCustomSlcOptions  //custom select test
							&& this.customSlcOptions.cols.tf_Has(colIndex));
		var optTxt = []; //custom selects text
		var activeFlt;
		if(this.refreshFilters && this.activeFilterId){
			activeFlt = this.activeFilterId.split('_')[0];
			activeFlt = activeFlt.split(this.prfxFlt)[1];
		}		
		
		for(var k=this.refRow; k<this.nbRows; k++)
		{
			// always visible rows don't need to appear on selects as always valid
			if( this.hasVisibleRows && this.visibleRows.tf_Has(k) && !this.paging ) 
				continue;

			var cells = tf_Tag(row[k],'td');
			var ncells = cells.length;

			if(ncells == this.nbCells && !isCustomSlc)
			{// checks if row has exact cell #
				for(var j=0; j<ncells; j++)
				{// this loop retrieves cell data
					if((colIndex==j && !this.refreshFilters) || 
						(colIndex==j && this.refreshFilters && ((row[k].style.display == '' && !this.paging) || 
						( this.paging && ((activeFlt==undefined || activeFlt==colIndex ) ||(activeFlt!=colIndex && this.validRowsIndex.tf_Has(k))) ))))
					{
						var cell_data = this.GetCellData(j, cells[j]);
						var cell_string = cell_data.tf_MatchCase(this.matchCase);//Váry Péter's patch
						// checks if celldata is already in array
						var isMatched = false;
						isMatched = optArray.tf_Has(cell_string,this.matchCase);
						
						if(!isMatched)
							optArray.push(cell_data);
					}
				}
			}
		}
		
		//Retrieves custom values
		if(isCustomSlc)
		{
			var customValues = this.__getCustomValues(colIndex);
			optArray = customValues[0];
			optTxt = customValues[1];
		}
		
		if(this.sortSlc && !isCustomSlc)
			optArray.sort(this.matchCase ? null : tf_IgnoreCaseSort);
		
		if(this.sortNumAsc && this.sortNumAsc.tf_Has(colIndex))
		{//asc sort
			try{
				optArray.sort( tf_NumSortAsc ); 
				if(isCustomSlc) optTxt.sort( tf_NumSortAsc );
			} catch(e) {
				optArray.sort(); 
				if(isCustomSlc) optTxt.sort();
			}//in case there are alphanumeric values
		}
		if(this.sortNumDesc && this.sortNumDesc.tf_Has(colIndex))
		{//desc sort
			try{
				optArray.sort( tf_NumSortDesc ); 
				if(isCustomSlc) optTxt.sort( tf_NumSortDesc );
			} catch(e) {
				optArray.sort(); 
				if(isCustomSlc) optTxt.sort();
			}//in case there are alphanumeric values
		}

		AddChecks();
			
		function AddCheck0()
		{// adds 1st option
			var li0 = tf_CreateCheckItem(o.fltIds[colIndex]+'_0', '', o.displayAllText);
			li0.className = o.checkListItemCssClass;
			ul.appendChild(li0);
			li0.check.onclick = function(){  
				o.__setCheckListValues(this); 
								
				if(o.refreshFilters){
					//o.activeFilterId = '';
					//o.RefreshFiltersGrid();
				}
				else
				ul.onchange.call(null);
			};
			
			if(tf_isIE)
			{//IE: label looses check capability
				li0.label.onclick = function(){ li0.check.click(); };
			}
		}
		
		function AddChecks()
		{		
			AddCheck0();
			
			var flts_values = [], fltArr = []; //remember grid values
			if(tf_CookieValueByIndex(o.fltsValuesCookie, colIndex)!=undefined)
				fltArr = tf_CookieValueByIndex(o.fltsValuesCookie, colIndex).split(' '+o.orOperator+' ');

			for(var y=0; y<optArray.length; y++)
			{
				var li = tf_CreateCheckItem(
					o.fltIds[colIndex]+'_'+(y+1), 
					optArray[y], 
					(isCustomSlc) ? optTxt[y] : optArray[y]
				);
				li.className = o.checkListItemCssClass;
				ul.appendChild(li);
				li.check.onclick = function(){ o.__setCheckListValues(this); ul.onchange.call(null); };
				
				/*** remember grid values ***/
				if(o.rememberGridValues)
				{
					if(fltArr.tf_Has(optArray[y].tf_MatchCase(o.matchCase),o.matchCase))
					{
						li.check.checked = true;
						o.__setCheckListValues(li.check);
					}			
				}
				
				if(tf_isIE)
				{//IE: label looses check capability
					li.label.onclick = function(){ this.firstChild.click(); };	
				}
			}
		}
		
		if(this.fillSlcOnDemand)
			flt.innerHTML = '';
		flt.appendChild(ul);
		flt.setAttribute('filled','1');
		
		/*** remember grid values IE only, items remain un-checked ***/
		if(o.rememberGridValues && tf_isIE)
		{
			var slcIndexes = ul.getAttribute('indexes');
			if(slcIndexes != null)
			{
				var indSplit = slcIndexes.split(',');//items indexes
				for(var n=0; n<indSplit.length; n++)
				{
					var cChk = tf_Id(this.fltIds[colIndex]+'_'+indSplit[n]); //checked item
					if(cChk) cChk.checked = true;
				}
			}
		}
	},
	
	Filter: function()
	{
		this.EvtManager(this.Evt.name.filter); 
	},
	_Filter: function()
	/*====================================================
		- Filtering fn
		- retrieves data from each td in every single tr
		and compares to search string for current
		column
		- tr is hidden if all search strings are not 
		found
	=====================================================*/
	{
		if( !this.fltGrid || (!this.hasGrid && !this.isFirstLoad) ) return;
		//invokes eventual onbefore method
		if(this.onBeforeFilter) this.onBeforeFilter.call(null,this);
		var row = this.tbl.rows;	
		f = this.fObj!=undefined ? this.fObj : [];
		var hiddenrows = 0;
		this.validRowsIndex = [];
		var o = this;		
		
		// removes keyword highlighting
		this.UnhighlightAll();

		// search args re-init
		this.searchArgs = this.GetFiltersValue(); 
		
		var num_cell_data, nbFormat;
		var re_le = new RegExp(this.leOperator), re_ge = new RegExp(this.geOperator);
		var re_l = new RegExp(this.lwOperator), re_g = new RegExp(this.grOperator);
		var re_d = new RegExp(this.dfOperator), re_lk = new RegExp(tf_RegexpEscape(this.lkOperator));
		var re_eq = new RegExp(this.eqOperator), re_st = new RegExp(this.stOperator);
		var re_en = new RegExp(this.enOperator), re_an = new RegExp(this.anOperator);
		var re_cr = new RegExp(this.curExp);
		
		function highlight(str,ok,cell){//keyword highlighting
			if( o.highlightKeywords && ok ){
				str = str.replace(re_lk,'');
				str = str.replace(re_eq,'');
				str = str.replace(re_st,'');
				str = str.replace(re_en,'');
				var w = str;
				if(re_le.test(str) || re_ge.test(str) || re_l.test(str) || re_g.test(str) || re_d.test(str))	
					w = tf_GetNodeText(cell);
				if(w!='')
					tf_HighlightWord( cell,w,o.highlightCssClass );
			}
		}
		
		//looks for search argument in current row
		function hasArg(sA,cell_data,j)
		{
			var occurence;
			//Search arg operator tests
			var hasLO = re_l.test(sA), hasLE = re_le.test(sA);
			var hasGR = re_g.test(sA), hasGE = re_ge.test(sA);
			var hasDF = re_d.test(sA), hasEQ = re_eq.test(sA);
			var hasLK = re_lk.test(sA), hasAN = re_an.test(sA);
			var hasST = re_st.test(sA), hasEN = re_en.test(sA);
			
			//Search arg dates tests
			var isLDate = ( hasLO && tf_isValidDate(sA.replace(re_l,''),dtType) );
			var isLEDate = ( hasLE && tf_isValidDate(sA.replace(re_le,''),dtType) );
			var isGDate = ( hasGR && tf_isValidDate(sA.replace(re_g,''),dtType) );
			var isGEDate = ( hasGE && tf_isValidDate(sA.replace(re_ge,''),dtType) );
			var isDFDate = ( hasDF && tf_isValidDate(sA.replace(re_d,''),dtType) );
			var isEQDate = ( hasEQ && tf_isValidDate(sA.replace(re_eq,''),dtType) );
						
			if( tf_isValidDate(cell_data,dtType) )
			{//dates
				var dte1 = tf_formatDate(cell_data,dtType);
				if(isLDate) 
				{// lower date
					var dte2 = tf_formatDate(sA.replace(re_l,''),dtType);
					occurence = (dte1 < dte2);
				}
				else if(isLEDate) 
				{// lower equal date
					var dte2 = tf_formatDate(sA.replace(re_le,''),dtType);
					occurence = (dte1 <= dte2);
				}
				else if(isGEDate) 
				{// greater equal date
					var dte2 = tf_formatDate(sA.replace(re_ge,''),dtType);
					occurence = (dte1 >= dte2);
				}
				else if(isGDate) 
				{// greater date
					var dte2 = tf_formatDate(sA.replace(re_g,''),dtType);
					occurence = (dte1 > dte2);
				}
				else if(isDFDate) 
				{// different date
					var dte2 = tf_formatDate(sA.replace(re_d,''),dtType);
					occurence = (dte1.toString() != dte2.toString());
				}
				else if(isEQDate) 
				{// equal date
					var dte2 = tf_formatDate(sA.replace(re_eq,''),dtType);
					occurence = (dte1.toString() == dte2.toString());
				}
				else if(re_lk.test(sA)) // searched keyword with * operator doesn't have to be a date
				{// like date
					occurence = o.__containsStr( sA.replace(re_lk,''),cell_data,null,false);
				}
				else if(tf_isValidDate(sA,dtType))
				{
					var dte2 = tf_formatDate(sA,dtType);
					occurence = (dte1.toString() == dte2.toString());
				}
			}
			
			else 
			{						
				//first numbers need to be formated
				if(o.hasColNbFormat && o.colNbFormat[j]!=null)
				{
					num_cell_data = tf_removeNbFormat(cell_data,o.colNbFormat[j]);
					nbFormat = o.colNbFormat[j];
				} else {
					if(o.thousandsSeparator==',' && o.decimalSeparator=='.')
					{
						num_cell_data = tf_removeNbFormat(cell_data,'us');
						nbFormat = 'us';
					} else {
						num_cell_data = tf_removeNbFormat(cell_data,'eu');
						nbFormat = 'eu';
					}
				}
				
				// first checks if there is any operator (<,>,<=,>=,!,*,=,{,})
				if(hasLE) //lower equal
					occurence = num_cell_data <= tf_removeNbFormat(sA.replace(re_le,''),nbFormat);
				
				else if(hasGE) //greater equal
					occurence = num_cell_data >= tf_removeNbFormat(sA.replace(re_ge,''),nbFormat);
				
				else if(hasLO) //lower
					occurence = num_cell_data < tf_removeNbFormat(sA.replace(re_l,''),nbFormat);
					
				else if(hasGR) //greater
					occurence = num_cell_data > tf_removeNbFormat(sA.replace(re_g,''),nbFormat);							
					
				else if(hasDF) //different
					occurence = o.__containsStr( sA.replace(re_d,''),cell_data ) ? false : true;
			
				else if(hasLK) //like
					occurence = o.__containsStr( sA.replace(re_lk,''),cell_data,null,false);
				
				else if(hasEQ) //equal
					occurence = o.__containsStr( sA.replace(re_eq,''),cell_data,null,true);
				
				else if(hasST) //starts with
					occurence = cell_data.indexOf(sA.replace(re_st,''))==0 ? true : false;
				
				else if(hasEN) //ends with
				{
					var searchArg = sA.replace(re_en,'');
					occurence = cell_data.lastIndexOf(searchArg,cell_data.length-1)==(cell_data.length-1)-(searchArg.length-1)
						&& cell_data.lastIndexOf(searchArg,cell_data.length-1) > -1
						? true : false;
				}
				
				else
					occurence = o.__containsStr( sA,cell_data,(f['col_'+j]==undefined) ? this.fltTypeInp : f['col_'+j] );
				
			}//else
			return occurence;
		}//fn
		
		for(var k=this.refRow; k<this.nbRows; k++)
		{
			/*** if table already filtered some rows are not visible ***/
			if(row[k].style.display == 'none') row[k].style.display = '';
					
			var cell = tf_Tag(row[k],'td');
			var nchilds = cell.length;			
			
			// checks if row has exact cell #
			if(nchilds != this.nbCells) continue;
	
			var occurence = [];
			var isRowValid = (this.searchType=='include') ? true : false;
			var singleFltRowValid = false; //only for single filter search
			
			for(var j=0; j<nchilds; j++)
			{// this loop retrieves cell data
				var sA = this.searchArgs[(this.singleSearchFlt) ? 0 : j]; //searched keyword
				var dtType = (this.hasColDateType) ? this.colDateType[j] : this.defaultDateType;
				if(sA=='') continue;
				
				var cell_data = this.GetCellData(j, cell[j]).tf_MatchCase(this.matchCase);
	
				var sAOrSplit = sA.split(this.orOperator);//multiple search parameter operator ||
				var hasMultiOrSA = (sAOrSplit.length>1) ? true : false;//multiple search || parameter boolean
				var sAAndSplit = sA.split('&&');//multiple search parameter operator &&
				var hasMultiAndSA = (sAAndSplit.length>1) ? true : false;//multiple search && parameter boolean

				if(hasMultiOrSA || hasMultiAndSA)
				{//multiple sarch parameters
					var cS, occur = false;
					var s = (hasMultiOrSA) ? sAOrSplit : sAAndSplit;
					for(var w=0; w<s.length; w++)
					{
						cS = s[w].tf_Trim();
						occur = hasArg(cS,cell_data,j);
						highlight(cS,occur,cell[j]);
						if(hasMultiOrSA && occur) break;
						if(hasMultiAndSA && !occur) break;
					}
					occurence[j] = occur;
				}
				else {//single search parameter		
					occurence[j] = hasArg(sA.tf_Trim(),cell_data,j);
					highlight(sA,occurence[j],cell[j]);
				}//else single param
				
				if(!occurence[j]) isRowValid = (this.searchType=='include') ? false : true;
				if(this.singleSearchFlt && occurence[j]) singleFltRowValid = true;
				
			}//for j
			
			if(this.singleSearchFlt && singleFltRowValid) isRowValid = true;
			
			if(!isRowValid)
			{
				this.SetRowValidation(k,false);
				// always visible rows need to be counted as valid
				if( this.hasVisibleRows && this.visibleRows.tf_Has(k) && !this.paging)
					this.validRowsIndex.push(k);
				else
					hiddenrows++;
			} else {
				this.SetRowValidation(k,true);
				this.validRowsIndex.push(k);
				this.SetRowBg(k,this.validRowsIndex.length);
				if(this.onRowValidated) this.onRowValidated.call(null,this,k);
			}
			
		}// for k
		
		this.nbVisibleRows = this.validRowsIndex.length;
		this.nbHiddenRows = hiddenrows;
		this.isStartBgAlternate = false;
		if( this.rememberGridValues ) this.RememberFiltersValue(this.fltsValuesCookie);
		if(!this.paging) this.ApplyGridProps();//applies filter props after filtering process
		if(this.paging){ 
			this.startPagingRow = 0; 
			this.currentPageNb = 1;
			this.SetPagingInfo(this.validRowsIndex); 
		}//starts paging process
		//invokes eventual onafter function
		if(this.onAfterFilter) this.onAfterFilter.call(null,this);
	},
	
	SetPagingInfo: function( validRows )
	/*====================================================
		- calculates page # according to valid rows
		- refreshes paging select according to page #
		- Calls GroupByPage method
	=====================================================*/
	{
		var row = this.tbl.rows;
		var mdiv = ( this.pagingTgtId==null ) ? this.mDiv : tf_Id( this.pagingTgtId );
		var pgspan = tf_Id(this.prfxPgSpan+this.id);
		
		if( validRows!=undefined ) this.validRowsIndex = validRows;//stores valid rows index
		else 
		{
			this.validRowsIndex = [];//re-sets valid rows index
	
			for(var j=this.refRow; j<this.nbRows; j++)//counts rows to be grouped 
			{
				var isRowValid = row[j].getAttribute('validRow');
				if(isRowValid=='true' || isRowValid==null )
						this.validRowsIndex.push(j);
			}//for j
		}
	
		this.nbPages = Math.ceil( this.validRowsIndex.length/this.pagingLength );//calculates nb of pages
		pgspan.innerHTML = this.nbPages; //refresh page nb span 
		if(this.pageSelectorType==this.fltTypeSlc) 
			this.pagingSlc.innerHTML = '';//select clearing shortcut
		
		if( this.nbPages>0 )
		{
			mdiv.style.visibility = 'visible';
			if(this.pageSelectorType==this.fltTypeSlc)
				for(var z=0; z<this.nbPages; z++)
				{
					var currOpt = new Option((z+1),z*this.pagingLength,false,false);
					this.pagingSlc.options[z] = currOpt;
				}
			else this.pagingSlc.value = this.currentPageNb; //input type
			
		} else {/*** if no results paging select and buttons are hidden ***/
			mdiv.style.visibility = 'hidden';
		}
		this.GroupByPage( this.validRowsIndex );
	},
	
	GroupByPage: function( validRows )
	/*====================================================
		- Displays current page rows
	=====================================================*/
	{
		var row = this.tbl.rows;
		var paging_end_row = parseInt( this.startPagingRow ) + parseInt( this.pagingLength );
		
		if( validRows!=undefined ) this.validRowsIndex = validRows;//stores valid rows index
	
		for(h=0; h<this.validRowsIndex.length; h++)
		{//this loop shows valid rows of current page
			if( h>=this.startPagingRow && h<paging_end_row )
			{
				var r = row[ this.validRowsIndex[h] ];
				if(r.getAttribute('validRow')=='true' || r.getAttribute('validRow')==undefined)
					r.style.display = '';
				this.SetRowBg(this.validRowsIndex[h],h);
			} else {
				row[ this.validRowsIndex[h] ].style.display = 'none';
				this.RemoveRowBg(this.validRowsIndex[h]);
			}
		}
		
		this.nbVisibleRows = this.validRowsIndex.length;
		this.isStartBgAlternate = false;
		this.ApplyGridProps();//re-applies filter behaviours after filtering process
	},
	
	ApplyGridProps: function()
	/*====================================================
		- checks methods that should be called
		after filtering and/or paging process
	=====================================================*/
	{
		if( this.activeFlt && this.activeFlt.nodeName.tf_LCase()==this.fltTypeSlc )
		{// blurs active filter (IE)
			this.activeFlt.blur(); 
			if(this.activeFlt.parentNode) this.activeFlt.parentNode.focus(); 
		}
		
		if( this.visibleRows ) this.SetVisibleRows();//shows rows always visible
		if( this.colOperation ) this.SetColOperation();//makes operation on a col
		if( this.refreshFilters ) this.RefreshFiltersGrid();//re-populates drop-down filters
		var nr = (!this.paging && this.hasVisibleRows) 
					? (this.nbVisibleRows - this.visibleRows.length) : this.nbVisibleRows;
		if( this.rowsCounter ) this.RefreshNbRows( nr );//refreshes rows counter
	},
	
	RefreshNbRows: function(p)
	/*====================================================
		- Shows total number of filtered rows
	=====================================================*/
	{
		if(this.rowsCounterSpan == null) return;
		var totTxt;
		if(!this.paging)
		{
			if(p!=undefined && p!='') totTxt=p;
			else totTxt = (this.nbFilterableRows - this.nbHiddenRows - (this.hasVisibleRows ? this.visibleRows.length : 0) );
		} else {
			var paging_start_row = parseInt(this.startPagingRow)+((this.nbVisibleRows>0) ? 1 : 0);//paging start row
			var paging_end_row = (paging_start_row+this.pagingLength)-1 <= this.nbVisibleRows 
				? (paging_start_row+this.pagingLength)-1 : this.nbVisibleRows;
			totTxt = paging_start_row+'-'+paging_end_row+' / '+this.nbVisibleRows;
		} 
		this.rowsCounterSpan.innerHTML = totTxt;
	},
	
	ChangePage: function( index )
	{
		this.EvtManager(this.Evt.name.changepage,{ pgIndex:index });
	},
	_ChangePage: function( index )
	/*====================================================
		- Changes page
		- Param:
			- index: option index of paging select 
			(numeric value)
	=====================================================*/
	{
		if( !this.paging ) return;
		if( index==undefined ) 
			index = (this.pageSelectorType==this.fltTypeSlc) ? 
				this.pagingSlc.options.selectedIndex : (this.pagingSlc.value-1);
		if( index>=0 && index<=(this.nbPages-1) )
		{
			this.currentPageNb = parseInt(index)+1;
			if(this.pageSelectorType==this.fltTypeSlc)
				this.pagingSlc.options[index].selected = true;
			else
				this.pagingSlc.value = this.currentPageNb;

			if( this.rememberPageNb ) this.RememberPageNb( this.pgNbCookie );
			this.startPagingRow = (this.pageSelectorType==this.fltTypeSlc)
				? this.pagingSlc.value : (index*this.pagingLength);
			this.GroupByPage();
		}
	},
	
	ChangeResultsPerPage: function()
	{
		this.EvtManager(this.Evt.name.changeresultsperpage);
	},
	_ChangeResultsPerPage: function()
	/*====================================================
		- calculates rows to be displayed in a page
		- method called by nb results per page select
	=====================================================*/
	{
		if( !this.paging ) return;
		var slcR = this.resultsPerPageSlc;
		var slcPagesSelIndex = (this.pageSelectorType==this.fltTypeSlc) 
			? this.pagingSlc.selectedIndex : parseInt(this.pagingSlc.value-1);
		this.pagingLength = parseInt(slcR.options[slcR.selectedIndex].value);
		this.startPagingRow = this.pagingLength*slcPagesSelIndex;

		if( !isNaN(this.pagingLength) )
		{
			if( this.startPagingRow>=this.nbFilterableRows )
				this.startPagingRow = (this.nbFilterableRows-this.pagingLength);
			this.SetPagingInfo();

			if(this.pageSelectorType==this.fltTypeSlc)
			{
				var slcIndex = (this.pagingSlc.options.length-1<=slcPagesSelIndex ) 
								? (this.pagingSlc.options.length-1) : slcPagesSelIndex;
				this.pagingSlc.options[slcIndex].selected = true;
			}
			if( this.rememberPageLen ) this.RememberPageLength( this.pgLenCookie );
		}//if isNaN
	},
	
	Sort: function()
	{
		this.EvtManager(this.Evt.name.sort);
	},
	
	GetColValues: function(colindex,num,exclude)
	/*====================================================
		- returns an array containing cell values of
		a column
		- needs following args:
			- column index (number)
			- a boolean set to true if we want only 
			numbers to be returned
			- array containing rows index to be excluded
			from returned values
	=====================================================*/
	{
		if( !this.fltGrid ) return;
		var row = this.tbl.rows;
		var colValues = [];
	
		for(var i=this.refRow; i<this.nbRows; i++)//iterates rows
		{
			var isExludedRow = false;
			if(exclude!=undefined && (typeof exclude).tf_LCase()=='object')
			{ // checks if current row index appears in exclude array
				isExludedRow = exclude.tf_Has(i); //boolean
			}
			var cell = tf_Tag(row[i],'td');
			var nchilds = cell.length;
			
			if(nchilds == this.nbCells && !isExludedRow)
			{// checks if row has exact cell # and is not excluded
				for(var j=0; j<nchilds; j++)// this loop retrieves cell data
				{
					if(j==colindex && row[i].style.display=='' )
					{
						var cell_data = this.GetCellData(j, cell[j]).tf_LCase();
						var nbFormat = this.colNbFormat ? this.colNbFormat[colindex] : null;
						(num) ? colValues.push( tf_removeNbFormat(cell_data,nbFormat) ) 
								: colValues.push( cell_data );
					}//if j==k
				}//for j
			}//if nchilds == this.nbCells
		}//for i
		return colValues;	
	},
	
	GetFilterValue: function(index)
	/*====================================================
		- Returns value of a specified filter
		- Params:
			- index: filter column index (numeric value)
	=====================================================*/
	{
		if( !this.fltGrid ) return;
		var fltValue;
		var flt = tf_Id(this.fltIds[index]);
		if(flt==null) return fltValue='';
		
		if( this['col'+index]!=this.fltTypeMulti && 
			this['col'+index]!=this.fltTypeCheckList )
			fltValue = flt.value;
		else if(this['col'+index] == this.fltTypeMulti)
		{//mutiple select
			fltValue = '';
			for(var j=0; j<flt.options.length; j++) 
				if(flt.options[j].selected)
					fltValue = fltValue.concat(
								flt.options[j].value+' ' +
								this.orOperator + ' '
								);
			//removes last operator ||
			fltValue = fltValue.substr(0,fltValue.length-4);
		}
		else if(this['col'+index]==this.fltTypeCheckList)
		{//checklist
			if(flt.getAttribute('value')!=null)
			{
				fltValue = flt.getAttribute('value');
				//removes last operator ||
				fltValue = fltValue.substr(0,fltValue.length-3);
			} else fltValue = '';
		}			
		return fltValue;
	},
	
	GetFiltersValue: function()
	/*====================================================
		- Returns the value of every single filter
	=====================================================*/
	{
		if( !this.fltGrid ) return;
		var searchArgs = [];
		for(var i=0; i<this.fltIds.length; i++)
			searchArgs.push(
				this.GetFilterValue(i).tf_MatchCase(this.matchCase).tf_Trim()
			);
		return searchArgs;
	},
	
	GetFilterId: function(index)
	/*====================================================
		- Returns filter id of a specified column
		- Params:
			- index: column index (numeric value)
	=====================================================*/
	{
		if( !this.fltGrid ) return;
		return this.fltIds[i];
	},
	
	GetFiltersByType: function(type,bool)
	/*====================================================
		- returns an array containing ids of filters of a 
		specified type (inputs or selects)
		- Note that hidden filters are also returned
		- Needs folllowing args:
			- filter type string ('input','select',
			'multiple')
			- optional boolean: if set true method
			returns column indexes otherwise filters ids
	=====================================================*/
	{
		if( !this.fltGrid ) return;
		var arr = [];
		for(var i=0; i<this.fltIds.length; i++)
		{
			var fltType = this['col'+i];
			if(fltType == type.tf_LCase())
			{
				var a = (bool) ? i : this.fltIds[i];
				arr.push(a);
			}
		}
		return arr;
	},
	
	GetCellsNb: function( rowIndex )
	/*====================================================
		- returns number of cells in a row
		- if rowIndex param is passed returns number of 
		cells of specified row (number)
	=====================================================*/
	{
		var tr = (rowIndex == undefined) ? this.tbl.rows[0] : this.tbl.rows[rowIndex];
		var n = tf_GetChildElms(tr);
		return n.childNodes.length;
	},
	
	GetRowsNb: function()
	/*====================================================
		- returns total nb of filterable rows starting 
		from reference row if defined
	=====================================================*/
	{
		var s = this.refRow==undefined ? 0 : this.refRow;
		var ntrs = this.tbl.rows.length;
		return parseInt(ntrs-s);
	},
	
	GetCellData: function(i, cell)
	/*====================================================
		- returns text content of a given cell
		- Params:
			- i: index of the column (number)
			- cell: td DOM object
	=====================================================*/
	{
		if(i==undefined || cell==null) return "";
		//First checks for customCellData event
		if(this.customCellData && this.customCellDataCols.tf_Has(i))
			return this.customCellData.call(null,this,cell,i);
		else
			return tf_GetNodeText(cell);
	},
	
	GetTableData: function()
	/*====================================================
		- returns an array containing table data:
		[rowindex,[value1,value2,value3...]]
	=====================================================*/
	{
		var row = this.tbl.rows;
		for(var k=this.refRow; k<this.nbRows; k++)
		{
			var rowData, cellData;
			rowData = [k,[]];
			var cells = tf_Tag(row[k],'td');
			for(var j=0; j<cells.length; j++)
			{// this loop retrieves cell data
				var cell_data = this.GetCellData(j, cells[j]);
				rowData[1].push( cell_data );
			}
			this.tblData.push( rowData )
		}
		return this.tblData;
	},
	
	GetFilteredData: function()
	/*====================================================
		- returns an array containing filtered data:
		[rowindex,[value1,value2,value3...]]
	=====================================================*/
	{
		if(!this.validRowsIndex) return [];
		var row = this.tbl.rows;
		var filteredData = [];
		for(var i=0; i<this.validRowsIndex.length; i++)
		{
			var rowData, cellData;
			rowData = [this.validRowsIndex[i],[]];
			var cells = tf_Tag(row[this.validRowsIndex[i]],'td');
			for(var j=0; j<cells.length; j++)
			{
				var cell_data = this.GetCellData(j, cells[j]);
				rowData[1].push( cell_data );
			}
			filteredData.push(rowData);
		}
		return filteredData;
	},
	
	GetFilteredDataCol: function(colIndex)
	/*====================================================
		- returns an array containing filtered data of a
		specified column. 
		- Params:
			- colIndex: index of the column (number)
		- returned array:
		[value1,value2,value3...]
	=====================================================*/
	{
		if(colIndex==undefined) return [];
		var data =  this.GetFilteredData();
		var colData = [];
		for(var i=0; i<data.length; i++)
		{
			var r = data[i];
			var d = r[1]; //cols values of current row
			var c = d[colIndex]; //data of searched column
			colData.push(c);
		}
		return colData;
	},
	
	GetRowDisplay: function(row)
	{
		if( !this.fltGrid && typeof row.tf_LCase!='object' ) return;
		return row.style.display;
	},
	
	SetRowValidation: function( rowIndex,isValid )
	/*====================================================
		- Validates/unvalidates row by setting 'validRow' 
		attribute and shows/hides row
		- Params:
			- rowIndex: index of the row (number)
			- isValid: boolean
	=====================================================*/
	{
		var row = this.tbl.rows[rowIndex];
		if( !row || (typeof isValid).tf_LCase()!='boolean' ) return;
	
		// always visible rows are valid
		if( this.hasVisibleRows && this.visibleRows.tf_Has(rowIndex) && !this.paging )
			isValid = true;
		
		var displayFlag = (isValid) ? '' : 'none';
		var validFlag = (isValid) ? 'true' : 'false';		
		row.style.display = displayFlag;
		
		if( this.paging ) 
			row.setAttribute('validRow',validFlag);
	},
	
	ValidateAllRows: function()
	/*====================================================
		- Validates all filterable rows
	=====================================================*/
	{
		if( !this.hasGrid ) return;
		this.validRowsIndex = [];
		for(var k=this.refRow; k<this.nbFilterableRows; k++)
		{
			this.SetRowValidation(k,true);
			this.validRowsIndex.push(k);
		}
	},
	
	SetFilterValue: function(index,searcharg,doFilter)
	/*====================================================
		- Inserts value in a specified filter
		- Params:
			- index: filter column index (numeric value)
			- searcharg: search string
			- doFilter: optional boolean for multiple
			selects: executes filtering when multiple 
			select populated... IE only!
	=====================================================*/
	{
		if( (!this.fltGrid && !this.isFirstLoad) || tf_Id(this.fltIds[index])==null ) return;
		var slc = tf_Id(this.fltIds[index]);
		var execFilter = (doFilter==undefined) ? true : doFilter;
		searcharg = (searcharg==undefined) ? '' : searcharg;
		
		if( this['col'+index]!=this.fltTypeMulti && 
			this['col'+index]!=this.fltTypeCheckList )
			slc.value = searcharg;
			
		else if(this['col'+index] == this.fltTypeMulti)
		{//multiple selects
			var s = searcharg.split(' '+this.orOperator+' ');
			var ct = 0; //keywords counter
			for(var j=0; j<slc.options.length; j++) 
			{
				if(s=='') slc.options[j].selected = false;
				if(slc.options[j].value=='') slc.options[j].selected = false;
				if(slc.options[j].value!='' && s.tf_Has(slc.options[j].value,true))
				{
					if(tf_isIE)
					{// IE multiple selection work-around
						//when last value reached filtering can be executed
						var filter = (ct==(s.length-1) && execFilter) ? true : false;
						this.__deferMultipleSelection(slc,j,filter);
						ct++;
					}					
					else
						slc.options[j].selected = true;
				}//if
			}//for j
		}
		
		else if(this['col'+index]==this.fltTypeCheckList)
		{//checklist
			searcharg = searcharg.tf_MatchCase(this.matchCase);
			var s = searcharg.split(' '+this.orOperator+' ');
			var fltValue = slc.setAttribute('value','');
			var fltIndex = slc.setAttribute('indexes','');
			for(var k=0; k<tf_Tag(slc,'li').length; k++) 
			{
				var li = tf_Tag(slc,'li')[k];
				var lbl = tf_Tag(li,'label')[0];
				var chk = tf_Tag(li,'input')[0];
				var lblTxt = tf_GetNodeText(lbl).tf_MatchCase(this.matchCase);
				if(lblTxt!='' && s.tf_Has(lblTxt,true))
				{
					chk.checked = true;
					this.__setCheckListValues(chk);
				}
				else{ 
					chk.checked = false;
					this.__setCheckListValues(chk);
				}
			}
		}
	},

	SetColWidths: function(rowIndex)
	/*====================================================
		- sets coluun widths in pixels
	=====================================================*/
	{
		if( !this.fltGrid || !this.hasColWidth ) return;
		var o = this, rIndex;
		if(rowIndex==undefined) rIndex = this.tbl.rows[0].style.display!='none' ? 0 : 1;
		else rIndex = rowIndex;
		setWidths( this.tbl.rows[rIndex] );

		function setWidths( row )
		{
			if( !o && (o.nbCells!=o.colWidth.length) ) return;
			if( o.nbCells==row.cells.length )
				for(var k=0; k<o.nbCells; k++)
					row.cells[k].style.width = o.colWidth[k];
		}
	},
	
	SetVisibleRows: function()
	/*====================================================
		- makes a row always visible
		- Note this works only if paging is false
	=====================================================*/
	{
		if( this.hasGrid && this.hasVisibleRows && !this.paging )
		{
			for(var i=0; i<this.visibleRows.length; i++)
			{
				if(this.visibleRows[i]<=this.nbRows)//row index cannot be > nrows
					this.SetRowValidation(this.visibleRows[i],true);
			}//for i
		}//if hasGrid
	},
	
	SetRowBg: function(rIndex,index)
	/*====================================================
		- sets row background color
		- Params:
			- rIndex: row index (numeric value)
			- index: valid row collection index needed to
			calculate bg color
	=====================================================*/
	{
		if(!this.alternateBgs || isNaN(rIndex)) return;
		var rows = this.tbl.rows;
		var i = (index==undefined) ? rIndex : index;
		this.RemoveRowBg(rIndex);
		tf_addClass(
			rows[rIndex],
			(i%2 == 0) ? this.rowBgEvenCssClass : this.rowBgOddCssClass
		);
	},
	
	RemoveRowBg: function(index)
	/*====================================================
		- removes row background color
		- Params:
			- index: row index (numeric value)
	=====================================================*/
	{
		if(isNaN(index)) return;
		var rows = this.tbl.rows;
		tf_removeClass(rows[index],this.rowBgOddCssClass);
		tf_removeClass(rows[index],this.rowBgEvenCssClass);
	},
	
	SetAlternateRows: function()
	/*====================================================
		- alternates row colors for better readability
	=====================================================*/
	{
		if( !this.hasGrid && !this.isFirstLoad ) return;
		var rows = this.tbl.rows;
		var noValidRowsIndex = this.validRowsIndex==null;
		var beginIndex = (noValidRowsIndex) ? this.refRow : 0; //1st index
		var indexLen = (noValidRowsIndex) // nb indexes
			? (this.nbFilterableRows+beginIndex) : this.validRowsIndex.length;

		for(var j=beginIndex; j<indexLen; j++)//alternates bg color
		{
			var rIndex = (noValidRowsIndex) ? j : this.validRowsIndex[j];
			this.SetRowBg(rIndex);
		}
	},
	
	RemoveAlternateRows: function()
	/*====================================================
		- removes alternate row colors
	=====================================================*/
	{
		if(!this.hasGrid) return;
		var row = this.tbl.rows;
		for(var i=this.refRow; i<this.nbRows; i++)
			this.RemoveRowBg(i);
		this.isStartBgAlternate = true;
	},
	
	SetColOperation: function()
	/*====================================================
		- Calculates values of a column
		- params are stored in 'colOperation' table's
		attribute
			- colOperation['id'] contains ids of elements 
			showing result (array)
			- colOperation['col'] contains index of 
			columns (array)
			- colOperation['operation'] contains operation
			type (array, values: sum, mean)
			- colOperation['write_method'] array defines 
			which method to use for displaying the 
			result (innerHTML, setValue, createTextNode).
			Note that innerHTML is the default value.
			- colOperation['tot_row_index'] defines in 
			which row results are displayed (integers array)
			
		- changes made by nuovella: 
		(1) optimized the routine (now it will only 
		process each column once),
		(2) added calculations for the median, lower and 
		upper quartile.
	=====================================================*/
	{
		if( !this.isFirstLoad && !this.hasGrid ) return;
		
		if(this.onBeforeOperation) this.onBeforeOperation.call(null,this);
		
		var labelId = this.colOperation['id'];
		var colIndex = this.colOperation['col'];
		var operation = this.colOperation['operation'];
		var outputType = this.colOperation['write_method'];
		var totRowIndex = this.colOperation['tot_row_index'];
		var excludeRow = this.colOperation['exclude_row'];
		var decimalPrecision = this.colOperation['decimal_precision']!=undefined
								? this.colOperation['decimal_precision'] : 2;
		
		//nuovella: determine unique list of columns to operate on
		var ucolIndex =[]; 
		var ucolMax=0;
		
		ucolIndex[ucolMax]=colIndex[0];
		
		for(var i=1; i<colIndex.length; i++)
		{
			saved=0;
			//see if colIndex[i] is already in the list of unique indexes
			for(var j=0; j<=ucolMax; j++ )
			{
				if (ucolIndex[j]==colIndex[i])
					saved=1;
			}
			if (saved==0)
			{//if not saved then, save the index;
				ucolMax++;
				ucolIndex[ucolMax]=colIndex[i];
			}
		}// for i
		
		if( (typeof labelId).tf_LCase()=='object' 
			&& (typeof colIndex).tf_LCase()=='object' 
			&& (typeof operation).tf_LCase()=='object' )
		{
			var row = this.tbl.rows;
			var colvalues = [];
			
			for(var ucol=0; ucol<=ucolMax; ucol++)
			{
				//this retrieves col values 
				//use ucolIndex because we only want to pass through this loop once for each column
				//get the values in this unique column
				colvalues.push( this.GetColValues(ucolIndex[ucol],true,excludeRow) );
				
			   //next: calculate all operations for this column
			   var result, nbvalues=0,  temp;
			   var meanValue=0, sumValue=0, minValue=null, maxValue=null, q1Value=null, medValue=null, q3Value=null;
			   var meanFlag=0, sumFlag=0, minFlag=0, maxFlag=0, q1Flag=0, medFlag=0, q3Flag=0;
			   var theList=[];
			   var opsThisCol=[], decThisCol=[], labThisCol=[], oTypeThisCol=[];
			   var mThisCol=-1;
				
				for(var i=0; i<colIndex.length; i++)
				{
					 if (colIndex[i]==ucolIndex[ucol])
					 {
						mThisCol++;
						opsThisCol[mThisCol]=operation[i].tf_LCase();
						decThisCol[mThisCol]=decimalPrecision[i];
						labThisCol[mThisCol]=labelId[i]; 
						oTypeThisCol = (outputType != undefined && (typeof outputType).tf_LCase()=='object') 
											? outputType[i] : null;
						
						switch( opsThisCol[mThisCol] )
						{			
							case 'mean':
								meanFlag=1;
							break;
							case 'sum':
								sumFlag=1;
							break;
							case 'min':
								minFlag=1;
							break;
							case 'max':
								maxFlag=1;
							break;
							case 'median':
								medFlag=1;	
								break;
							case 'q1':
								q1Flag=1;
							break;
							case 'q3':
								q3Flag=1;
							break;
						}
					}		
				}
				
				for(var j=0; j<colvalues[ucol].length; j++ )
				{
					if ((q1Flag==1)||(q3Flag==1) || (medFlag==1))
					{//sort the list for calculation of median and quartiles
						if (j<colvalues[ucol].length -1)
						{
							for(k=j+1;k<colvalues[ucol].length; k++) {
				  
								if( eval(colvalues[ucol][k]) < eval(colvalues[ucol][j]))
								{
									temp = colvalues[ucol][j];            
									colvalues[ucol][j] = colvalues[ucol][k];              
									colvalues[ucol][k] = temp;            
								}
							}
						}
					}
					var cvalue = parseFloat(colvalues[ucol][j]);
					theList[j]=parseFloat( cvalue );
	
					if( !isNaN(cvalue) )
					{
						nbvalues++;
						if ((sumFlag==1)|| (meanFlag==1)) sumValue += parseFloat( cvalue );
						if (minFlag==1) 
						{
							if (minValue==null)
							{
								minValue = parseFloat( cvalue );
							}
							else minValue= parseFloat( cvalue )<minValue? parseFloat( cvalue ): minValue;
						}
						if (maxFlag==1) {
							if (maxValue==null)
							{maxValue = parseFloat( cvalue );}
						else {maxValue= parseFloat( cvalue )>maxValue? parseFloat( cvalue ): maxValue;}
						}
					}
				}//for j
				if (meanFlag==1) meanValue = sumValue/nbvalues;
				if (medFlag==1)
				{
						var aux = 0;
						if(nbvalues%2 == 1) 
						{
							aux = Math.floor(nbvalues/2);
							medValue = theList[aux];   
						}
					else medValue = (theList[nbvalues/2]+theList[((nbvalues/2)-1)])/2;
				}
				if (q1Flag==1)
				{	
					var posa=0.0;
					posa = Math.floor(nbvalues/4);
					if (4*posa == nbvalues) {q1Value = (theList[posa-1] + theList[posa])/2;}
					else {q1Value = theList[posa];}
				}
				if (q3Flag==1)
				{
					var posa=0.0;
					var posb=0.0;
					posa = Math.floor(nbvalues/4);
					if (4*posa == nbvalues)
					{
						posb = 3*posa;
						q3Value = (theList[posb] + theList[posb-1])/2;  
					}
					else
						q3Value = theList[nbvalues-posa-1];
				}
				
				for(var i=0; i<=mThisCol; i++ )
				{
				   switch( opsThisCol[i] )
				   {			
						case 'mean':
							result=meanValue;
						break;
						case 'sum':
							result=sumValue;
						break;
						case 'min':
							result=minValue;
						break;
						case 'max':
							result=maxValue;
						break;
						case 'median':
							result=medValue;	
							break;
						case 'q1':
							result=q1Value;
						break;
						case 'q3':
							result=q3Value;
						break;
				  }		
					
				var precision = decThisCol[i]!=undefined && !isNaN( decThisCol[i] )
									? decThisCol[i] : 2;

				if(oTypeThisCol!=null && result)
				{//if outputType is defined
					result = result.toFixed( precision );
					if( tf_Id( labThisCol[i] )!=undefined )
					{
						switch( oTypeThisCol.tf_LCase() )
						{
							case 'innerhtml':							
								if (isNaN(result) || !isFinite(result) || (nbvalues==0)) 
									tf_Id( labThisCol[i] ).innerHTML = '.';
								else
									tf_Id( labThisCol[i] ).innerHTML = result;
							break;
							case 'setvalue':
								tf_Id( labThisCol[i] ).value = result;
							break;
							case 'createtextnode':
								var oldnode = tf_Id( labThisCol[i] ).firstChild;
								var txtnode = tf_CreateText( result );
								tf_Id( labThisCol[i] ).replaceChild( txtnode,oldnode );
							break;
						}//switch
					}
				} else {
					try
					{      
						if (isNaN(result) || !isFinite(result) || (nbvalues==0)) 
							tf_Id( labThisCol[i] ).innerHTML = '.';
						else
							 tf_Id( labThisCol[i] ).innerHTML = result.toFixed( precision );
					} catch(e){ }//catch
				}//else
			 }//for i
			//eventual row(s) with result are always visible
			if(totRowIndex!=undefined && row[totRowIndex[ucol]]) 
				row[totRowIndex[ucol]].style.display = '';
			}//for ucol
		}//if typeof
		
		if(this.onAfterOperation) this.onAfterOperation.call(null,this);
	},
	
	SetPage: function( cmd )
	/*====================================================
		- If paging set true shows page according to
		param value (string or number):
			- strings: 'next','previous','last','first' or
			- number: page number
	=====================================================*/
	{
		if( this.hasGrid && this.paging )
		{
			var btnEvt = this.pagingBtnEvents, cmdtype = typeof cmd;
			if(cmdtype=='string')
			{
				switch(cmd.tf_LCase())
				{
					case 'next':
						btnEvt.next();
					break;
					case 'previous':
						btnEvt.prev();
					break;
					case 'last':
						btnEvt.last();
					break;
					case 'first':
						btnEvt.first();
					break;
					default:
						btnEvt.next();
					break;
				}//switch
			}
			if(cmdtype=='number') this.ChangePage( (cmd-1) );
		}// this.hasGrid 
	},
	
	RefreshFiltersGrid: function()
	/*====================================================
		- retrieves select, multiple and checklist filters
		- calls method repopulating filters
	=====================================================*/
	{
		var slcA1 = this.GetFiltersByType( this.fltTypeSlc,true );
		var slcA2 = this.GetFiltersByType( this.fltTypeMulti,true );
		var slcA3 = this.GetFiltersByType( this.fltTypeCheckList,true );
		var slcIndex = slcA1.concat(slcA2);
		slcIndex = slcIndex.concat(slcA3);

		if( this.activeFilterId!=null )//for paging
		{
			var activeFlt = this.activeFilterId.split('_')[0];
			activeFlt = activeFlt.split(this.prfxFlt)[1];
			var slcSelectedValue;
			for(var i=0; i<slcIndex.length; i++)
			{
				var curSlc = tf_Id(this.fltIds[slcIndex[i]]);
				slcSelectedValue = this.GetFilterValue( slcIndex[i] );
				//if(activeFlt==slcIndex[i] && slcA3.tf_Has(slcIndex[i]) && slcSelectedValue!=this.displayAllText) continue;
				if(activeFlt!=slcIndex[i] || (this.paging && slcA1.tf_Has(slcIndex[i]) && activeFlt==slcIndex[i] ) || 
					( !this.paging && (slcA3.tf_Has(slcIndex[i]) || slcA2.tf_Has(slcIndex[i]) ) /*&& activeFlt==slcIndex[i]*/) || 
					slcSelectedValue==this.displayAllText )
					//(this.paging && (!slcA3.tf_Has(slcIndex[i]) && !slcA2.tf_Has(slcIndex[i]) && activeFlt==slcIndex[i]) ) )
				{
					if(slcA3.tf_Has(slcIndex[i]))
						this.checkListDiv[slcIndex[i]].innerHTML = '';
					else curSlc.innerHTML = '';
					
					if(this.fillSlcOnDemand) { //1st option needs to be inserted
						var opt0 = tf_CreateOpt(this.displayAllText,'');
						curSlc.appendChild( opt0 );
					}
					
					if(slcA3.tf_Has(slcIndex[i]))
						this._PopulateCheckList(slcIndex[i]);
					else
						this._PopulateSelect(slcIndex[i],true);
						
					this.SetFilterValue(slcIndex[i],slcSelectedValue);
				}
			}// for i
		}
	},
	
	RememberFiltersValue: function( name )
	/*==============================================
		- stores filters' values in a cookie
		when Filter() method is called
		- Params:
			- name: cookie name (string)
		- credits to Florent Hirchy
	===============================================*/
	{
		var flt_values = [];
		for(var i=0; i<this.fltIds.length; i++)
		{//creates an array with filters' values
			value = this.GetFilterValue(i);
			if (value == '') value = ' ';
			flt_values.push(value);
		}
		flt_values.push(this.fltIds.length); //adds array size
		tf_WriteCookie(
			name,
			flt_values,
			this.cookieDuration
		); //writes cookie  
	},
	
	RememberPageNb: function( name )
	/*==============================================
		- stores page number value in a cookie
		when ChangePage method is called
		- Params:
			- name: cookie name (string)
	===============================================*/
	{
		tf_WriteCookie(
			name,
			this.currentPageNb,
			this.cookieDuration
		); //writes cookie  
	},
	
	RememberPageLength: function( name )
	/*==============================================
		- stores page length value in a cookie
		when ChangePageLength method is called
		- Params:
			- name: cookie name (string)
	===============================================*/
	{
		tf_WriteCookie(
			name,
			this.resultsPerPageSlc.selectedIndex,
			this.cookieDuration
		); //writes cookie
	},
	
	ResetValues: function()
	{ 
		this.EvtManager(this.Evt.name.resetvalues); 
	},
	
	_ResetValues: function()
	/*==============================================
		- re-sets grid values when page is 
		re-loaded. It invokes ResetGridValues,
		ResetPage and ResetPageLength methods
		- Params:
			- name: cookie name (string)
	===============================================*/
	{
		if(this.rememberGridValues && this.fillSlcOnDemand) //only fillSlcOnDemand
			this.ResetGridValues(this.fltsValuesCookie);
		if(this.rememberPageLen) this.ResetPageLength( this.pgLenCookie );
		if(this.rememberPageNb) this.ResetPage( this.pgNbCookie );		
	},	
	
	ResetGridValues: function( name )
	/*==============================================
		- re-sets filters' values when page is 
		re-loaded if load on demand is enabled
		- Params:
			- name: cookie name (string)
		- credits to Florent Hirchy
	===============================================*/
	{
		if(!this.fillSlcOnDemand) return;
		var flts = tf_ReadCookie(name); //reads the cookie
		var reg = new RegExp(',','g');	
		var flts_values = flts.split(reg); //creates an array with filters' values
		var slcFltsIndex = this.GetFiltersByType(this.fltTypeSlc, true);
		var multiFltsIndex = this.GetFiltersByType(this.fltTypeMulti, true);
		
		if(flts_values[(flts_values.length-1)] == this.fltIds.length)
		{//if the number of columns is the same as before page reload
			for(var i=0; i<(flts_values.length - 1); i++)
			{			
				if (flts_values[i]==' ') continue;				
				if(this['col'+i]==this.fltTypeSlc || this['col'+i]==this.fltTypeMulti)
				{// if fillSlcOnDemand, drop-down needs to contain stored value(s) for filtering
					var slc = tf_Id( this.fltIds[i] );
					slc.options[0].selected = false;
					
					if( slcFltsIndex.tf_Has(i) )
					{//selects
						var opt = tf_CreateOpt(flts_values[i],flts_values[i],true);
						slc.appendChild(opt);
						this.hasStoredValues = true;
					}
					if(multiFltsIndex.tf_Has(i))
					{//multiple select
						var s = flts_values[i].split(' '+this.orOperator+' ');
						for(j=0; j<s.length; j++)
						{
							if(s[j]=='') continue;
							var opt = tf_CreateOpt(s[j],s[j],true);
							slc.appendChild(opt);
							this.hasStoredValues = true;
							
							if(tf_isIE)
							{// IE multiple selection work-around
								this.__deferMultipleSelection(slc,j,false);
								hasStoredValues = false;
							}
						}
					}// if multiFltsIndex
				}
				else if(this['col'+i]==this.fltTypeCheckList)
				{
					var divChk = this.checkListDiv[i];
					divChk.title = divChk.innerHTML;
					divChk.innerHTML = '';
					
					var ul = tf_CreateElm('ul',['id',this.fltIds[i]],['colIndex',i]);
					ul.className = this.checkListCssClass;

					var li0 = tf_CreateCheckItem(this.fltIds[i]+'_0', '', this.displayAllText);
					li0.className = this.checkListItemCssClass;
					ul.appendChild(li0);

					divChk.appendChild(ul);
					
					var s = flts_values[i].split(' '+this.orOperator+' ');
					for(j=0; j<s.length; j++)
					{
						if(s[j]=='') continue;
						var li = tf_CreateCheckItem(this.fltIds[i]+'_'+(j+1), s[j], s[j]);
						li.className = this.checkListItemCssClass;
						ul.appendChild(li);
						li.check.checked = true;
						this.__setCheckListValues(li.check);
						this.hasStoredValues = true;
					}					
				}
			}//end for
			
			if(!this.hasStoredValues && this.paging) this.SetPagingInfo();
		}//end if
	},
	
	ResetPage: function( name )
	{
		this.EvtManager(this.Evt.name.resetpage);
	},
	_ResetPage: function( name )
	/*==============================================
		- re-sets page nb at page re-load
		- Params:
			- name: cookie name (string)
	===============================================*/
	{
		var pgnb = tf_ReadCookie(name); //reads the cookie
		if( pgnb!='' ) 
			this.ChangePage((pgnb-1));
	},
	
	ResetPageLength: function( name )
	{
		this.EvtManager(this.Evt.name.resetpagelength);
	},
	_ResetPageLength: function( name )
	/*==============================================
		- re-sets page length at page re-load
		- Params:
			- name: cookie name (string)
	===============================================*/
	{
		if(!this.paging) return;
		var pglenIndex = tf_ReadCookie(name); //reads the cookie
		
		if( pglenIndex!='' )
		{
			this.resultsPerPageSlc.options[pglenIndex].selected = true;
			this.ChangeResultsPerPage();
		}
	},
	
	SetLoader: function()
	/*====================================================
		- generates loader div
	=====================================================*/
	{
		if( this.loaderDiv!=null ) return;
		var containerDiv = tf_CreateElm( 'div',['id',this.prfxLoader+this.id] );
		containerDiv.className = this.loaderCssClass;// for ie<=6
		//containerDiv.style.display = 'none';
		var targetEl = (this.loaderTgtId==null) 
			? (this.gridLayout ? this.tblCont : this.tbl.parentNode) : tf_Id( this.loaderTgtId );
		if(this.loaderTgtId==null) targetEl.insertBefore(containerDiv, this.tbl);
		else targetEl.appendChild( containerDiv );
		this.loaderDiv = tf_Id(this.prfxLoader+this.id);
		if(this.loaderHtml==null) 
			this.loaderDiv.appendChild( tf_CreateText(this.loaderText) );
		else this.loaderDiv.innerHTML = this.loaderHtml;
	},
	
	RemoveLoader: function()
	/*====================================================
		- removes loader div
	=====================================================*/
	{
		if( this.loaderDiv==null ) return;
		var targetEl = (this.loaderTgtId==null) 
			? (this.gridLayout ? this.tblCont : this.tbl.parentNode) : tf_Id( this.loaderTgtId );
		targetEl.removeChild(this.loaderDiv);
		this.loaderDiv = null;
	},
	
	ShowLoader: function(p)
	/*====================================================
		- displays/hides loader div
	=====================================================*/
	{
		if(!this.loader || !this.loaderDiv) return;
		if(this.loaderDiv.style.display==p) return;
		var o = this;

		function displayLoader(){ 
			if(!o.loaderDiv) return;
			if(o.onShowLoader && p!='none') 
				o.onShowLoader.call(null,o);
			o.loaderDiv.style.display = p;
			if(o.onHideLoader && p=='none') 
				o.onHideLoader.call(null,o);
		}

		var t = (p=='none') ? this.loaderCloseDelay : 1;
		window.setTimeout(displayLoader,t);
	},
	
	StatusMsg: function(t)
	/*====================================================
		- sets status messages
	=====================================================*/
	{
		if(t==undefined) this.StatusMsg('');
		if(this.status) this.WinStatusMsg(t);
		if(this.statusBar) this.StatusBarMsg(t);
	},
	
	StatusBarMsg: function(t)
	/*====================================================
		- sets status bar messages
	=====================================================*/
	{
		if(!this.statusBar || !this.statusBarSpan) return;
		var o = this;
		function setMsg(){
			o.statusBarSpan.innerHTML = t;
		}
		var d = (t=='') ? (this.statusBarCloseDelay) : 1;
		window.setTimeout(setMsg,d);
	},
	
	WinStatusMsg: function(t)
	/*====================================================
		- sets window status messages
	=====================================================*/
	{
		if(!this.status) return;
		window.status = t;
	},
	
	ClearFilters: function()
	{ 
		this.EvtManager(this.Evt.name.clear); 
	},	
	_ClearFilters: function()
	/*====================================================
		- clears grid filters
	=====================================================*/
	{
		if( !this.fltGrid ) return;
		for(var i=0; i<this.fltIds.length; i++)
			this.SetFilterValue(i,'');
		if(this.refreshFilters){
			this.activeFilterId = '';	
			this.RefreshFiltersGrid();
		}
		if(this.rememberPageLen) tf_RemoveCookie(this.pgLenCookie);
		if(this.rememberPageNb) tf_RemoveCookie(this.pgNbCookie);
	},
	
	UnhighlightAll: function()
	/*====================================================
		- removes keyword highlighting
	=====================================================*/
	{
		if( this.highlightKeywords && this.searchArgs!=null )
			for(var y=0; y<this.searchArgs.length; y++)
				tf_UnhighlightWord( 
					this.tbl,this.searchArgs[y],
					this.highlightCssClass 
				);
	},
	
	RefreshGrid: function()
	/*====================================================
		- Re-generates filters grid
	=====================================================*/
	{
		this.RemoveGrid();
		setFilterGrid(this.id, this.startRow, this.fObj);
	},
	
	__resetGrid: function()
	/*====================================================
		- Only used by AddGrid() method
		- Resets filtering grid bar if previously removed
	=====================================================*/
	{
		if( this.isFirstLoad ) return;
		
		// grid was removed, grid row element is stored in this.fltGridEl property
		this.tbl.rows[this.filtersRowIndex].parentNode.insertBefore( 
			this.fltGridEl,
			this.tbl.rows[this.filtersRowIndex]
		);
		
		if( this.isExternalFlt )
		{// filters are appended in external placeholders elements
			for(var ct=0; ct<this.externalFltTgtIds.length; ct++ )
				if( tf_Id(this.externalFltTgtIds[ct]) )
					tf_Id(this.externalFltTgtIds[ct]).appendChild(this.externalFltEls[ct]);
		}
		
		this.nbFilterableRows = this.GetRowsNb();
		this.nbVisibleRows = this.nbFilterableRows;
		this.nbRows = this.tbl.rows.length;
		this.sort = true;
		
		/*** 	ie bug work-around, filters need to be re-generated
				since row is empty; insertBefore method doesn't seem to work properly 
				with previously generated DOM nodes modified by innerHTML 	***/
		if( this.tbl.rows[this.filtersRowIndex].innerHTML=='' )
		{
			this.tbl.deleteRow(this.filtersRowIndex);
			this.RemoveGrid();
			this.RemoveExternalFlts();
			this.fltIds = [];
			this.isFirstLoad = true;
			this.AddGrid();
			
		}
		
		this.hasGrid = true;
	},
	
	__deferMultipleSelection: function(slc,index,filter)
	/*====================================================
		- IE bug: it seems there is no way to make 
		multiple selections programatically, only last 
		selection is kept (multiple select previously 
		populated via DOM)
		- Turn-around: defer selection with a setTimeout
		If you find an alternative elegant solution to 
		this let me know ;-)
		- For the moment only this solution seems 
		to work!
		- Params: 
			- slc = select object (select obj)
			- index to be selected (integer)
			- execute filtering (boolean)
	=====================================================*/
	{
		if(slc.nodeName.tf_LCase() != 'select') return;
		var doFilter = (filter==undefined) ? false : filter;
		var o = this;
		window.setTimeout(
			function(){
				slc.options[0].selected = false;
				
				if(slc.options[index].value=='') 
					slc.options[index].selected = false;
				else
				slc.options[index].selected = true; 
				if(doFilter) o.Filter();
			},
			.1
		);
	},
	
	__getCustomValues: function(colIndex)
	/*====================================================
		- Returns an array [[values],[texts]] with 
		custom values for a given filter
		- Param: column index (integer)
	=====================================================*/
	{
		if(colIndex==undefined) return;
		var isCustomSlc = (this.hasCustomSlcOptions  //custom select test
							&& this.customSlcOptions.cols.tf_Has(colIndex));
		if(!isCustomSlc) return;
		var optTxt = [], optArray = [];
		var index = this.customSlcOptions.cols.tf_IndexByValue(colIndex);
		var slcValues = this.customSlcOptions.values[index];
		var slcTexts = this.customSlcOptions.texts[index];
		var slcSort = this.customSlcOptions.sorts[index];
		for(var r=0; r<slcValues.length; r++)
		{
			optArray.push(slcValues[r]);
			if(slcTexts[r]!=undefined)
				optTxt.push(slcTexts[r]);
			else
				optTxt.push(slcValues[r]);
		}
		if(slcSort)
		{
			optArray.sort();
			optTxt.sort();
		}
		return [optArray,optTxt];
	},
	
	__setCheckListValues: function(o)
	/*====================================================
		- Sets checked items information of a checklist
	=====================================================*/
	{
		if(o==null) return;
		var chkValue = o.value; //checked item value
		var chkIndex = parseInt(o.id.split('_')[2]);
		var filterTag = 'ul', itemTag = 'li';
		var n = o;
		
		//ul tag search
		while(n.nodeName.tf_LCase() != filterTag)
			n = n.parentNode;

		if(n.nodeName.tf_LCase() != filterTag) return;
		
		var li = n.childNodes[chkIndex];
		var colIndex = n.getAttribute('colIndex');
		var fltValue = n.getAttribute('value'); //filter value (ul tag)
		var fltIndexes = n.getAttribute('indexes'); //selected items (ul tag)

		if(o.checked)		
		{
			if(chkValue=='')
			{//show all item
				if((fltIndexes!=null && fltIndexes!=''))
				{
					var indSplit = fltIndexes.split(this.separator);//items indexes
					for(var u=0; u<indSplit.length; u++)
					{//checked items loop
						var cChk = tf_Id(this.fltIds[colIndex]+'_'+indSplit[u]); //checked item
						if(cChk)
						{ 
							cChk.checked = false;
							tf_removeClass(
								n.childNodes[indSplit[u]],
								this.checkListSlcItemCssClass
							);
						}
					}
				}
				n.setAttribute('value', '');
				n.setAttribute('indexes', '');
				
			} else {
				fltValue = (fltValue) ? fltValue : '';
				chkValue = (fltValue+' '+chkValue +' '+this.orOperator).tf_Trim();
				chkIndex = fltIndexes + chkIndex + this.separator;
				n.setAttribute('value', chkValue );
				n.setAttribute('indexes', chkIndex);
				//1st option unchecked
				if(tf_Id(this.fltIds[colIndex]+'_0'))
					tf_Id(this.fltIds[colIndex]+'_0').checked = false; 
			}
			
			if(li.nodeName.tf_LCase() == itemTag)
			{
				tf_removeClass(n.childNodes[0],this.checkListSlcItemCssClass);
				tf_addClass(li,this.checkListSlcItemCssClass);
			}
		} else { //removes values and indexes
			if(chkValue!='')
			{
				var replaceValue = new RegExp(tf_RegexpEscape(chkValue+' '+this.orOperator));
				fltValue = fltValue.replace(replaceValue,'');
				n.setAttribute('value', fltValue);
				
				var replaceIndex = new RegExp(tf_RegexpEscape(chkIndex + this.separator));
				fltIndexes = fltIndexes.replace(replaceIndex,'');
				n.setAttribute('indexes', fltIndexes);
			}
			if(li.nodeName.tf_LCase() == itemTag)
				tf_removeClass(li,this.checkListSlcItemCssClass);
		}
			
	},
	
	__containsStr: function(arg,data,fltType,forceMatch)
	/*==============================================
		- Checks if data contains searched arg,
		returns a boolean
		- Params:
			- arg: searched string
			- data: data string
			- fltType: filter type (string, 
			exact match by default for selects - 
			optional)
			- forceMatch: boolean forcing exact
			match (optional)
	===============================================*/
	{
		// Improved by Cedric Wartel (cwl)
		// automatic exact match for selects and special characters are now filtered
		var regexp;
		var modifier = (this.matchCase) ? 'g' : 'gi';
		var exactMatch = (forceMatch==undefined) ? this.exactMatch : forceMatch;
		if(exactMatch || (fltType!=this.fltTypeInp && fltType!=undefined))//Váry Péter's patch
			regexp = new RegExp('(^\\s*)'+tf_RegexpEscape(arg)+'(\\s*$)', modifier);							
		else
			regexp = new RegExp(tf_RegexpEscape(arg), modifier);
		return regexp.test(data);
	},
	
	IncludeFile: function(fileId, filePath, callback, type)
	{
		var ftype = (type==undefined) ? 'script' : type;
		var isImported = tf_isImported(filePath, ftype);
		if( isImported ) return;
		
		var o = this, isLoaded = false, file;			
		var head = tf_Tag(document,'head')[0];
		
		if(ftype.tf_LCase() == 'link')
			file = tf_CreateElm(
						'link', ['id',fileId], ['type','text/css'],
						['rel','stylesheet'], ['href',filePath]
					);
		else
			file = tf_CreateElm(
						'script', ['id',fileId], 
						['type','text/javascript'], ['src',filePath]
					);

		file.onload = file.onreadystatechange = function()
		{
			if (!isLoaded && 
				(!this.readyState || this.readyState == 'loaded' || this.readyState == 'complete')) 
			{
				isLoaded = true;
				if (typeof callback === 'function')
					callback(o);
			}
		}
		head.appendChild(file);
	},
	
	/*====================================================
		- Additional public methods for developers
	=====================================================*/
	
	HasGrid: function()
	/*====================================================
		- checks if table has a filter grid
		- returns a boolean
	=====================================================*/
	{
		return this.hasGrid;
	},
	
	GetFiltersId: function()
	/*====================================================
		- returns an array containing filters ids
		- Note that hidden filters are also returned
	=====================================================*/
	{
		if( !this.hasGrid ) return;
		return this.fltIds;
	},
	
	GetValidRowsIndex: function()
	/*====================================================
		- returns an array containing valid rows indexes 
		(valid rows upon filtering)
	=====================================================*/
	{
		if( !this.hasGrid ) return;
		return this.validRowsIndex;
	},
	
	GetFiltersRowIndex: function()
	/*====================================================
		- Returns the index of the row containing the 
		filters
	=====================================================*/
	{
		if( !this.hasGrid ) return;
		return this.filtersRowIndex;
	},
	
	GetHeadersRowIndex: function()
	/*====================================================
		- Returns the index of the headers row
	=====================================================*/
	{
		if( !this.hasGrid ) return;
		return this.headersRow;
	},
	
	GetStartRowIndex: function()
	/*====================================================
		- Returns the index of the row from which will 
		start the filtering process (1st filterable row)
	=====================================================*/
	{
		if( !this.hasGrid ) return;
		return this.refRow;
	},
	
	GetLastRowIndex: function()
	/*====================================================
		- Returns the index of the last row
	=====================================================*/
	{
		if( !this.hasGrid ) return;
		return (this.nbRows-1);
	},
	
	AddPaging: function(filterTable)
	/*====================================================
		- Adds paging feature if filter grid bar is 
		already set
		- Param(s):
			- execFilter: if true table is filtered 
			(boolean)
	=====================================================*/
	{
		if( !this.hasGrid || this.paging ) return;
		this.paging = true; 
		this.isPagingRemoved = true; 
		this.SetPaging();
		if(filterTable) this.Filter();
	}	
	
}

/* --- */

/*====================================================
	- General TF utility fns below
=====================================================*/

function tf_GetChildElms(n)
/*====================================================
	- checks passed node is a ELEMENT_NODE nodeType=1
	- removes TEXT_NODE nodeType=3  
=====================================================*/
{
	if(n!=undefined && n.nodeType == 1)
	{
		var enfants = n.childNodes;
		for(var i=0; i<enfants.length; i++)
		{
			var child = enfants[i];
			if(child.nodeType == 3)
			{ 
				n.removeChild(child);
				i = -1;
			}
		}
		return n;	
	}
}

function tf_GetNodeText(n)
/*====================================================
	- returns text + text of child nodes of a node
=====================================================*/
{
	/*if(n.innerText) return n.innerText.tf_Trim();
	var s = '';
	var enfants = n.childNodes;
	for(var i=0; i<enfants.length; i++)
	{
		var child = enfants[i];
		if(child.nodeType == 3) s+= child.data;
		else s+= tf_GetNodeText(child).tf_Trim();
	}*/
	var s = n.textContent || n.innerText || n.innerHTML.replace(/\<[^<>]+>/g, '');
	return s.replace(/^\s+/, '').replace(/\s+$/, '');

	return s.tf_Trim();
}

function tf_isObj(varname)
/*====================================================
	- checks if var exists and is an object
	- returns a boolean
=====================================================*/
{
	var isO = false;
	if( window[varname] && (typeof window[varname]).tf_LCase()=='object' )
		isO = true;
	return isO;
}

function tf_isFn(fn)
/*====================================================
	- checks if passed param is a function
	- returns a boolean
=====================================================*/
{
	var isFn = false;
	if(fn && (typeof fn).tf_LCase() == 'function')
		isFn = true;
	return isFn;
}

function tf_Id(id)
/*====================================================
	- this is just a getElementById shortcut
=====================================================*/
{
	return document.getElementById( id );
}

function tf_Tag(o,tagname)
/*====================================================
	- this is just a getElementsByTagName shortcut
=====================================================*/
{
	return o.getElementsByTagName( tagname );
}

function tf_RegexpEscape(s)
/*====================================================
	- escapes special characters [\^$.|?*+() 
	for regexp
	- Many thanks to Cedric Wartel for this fn
=====================================================*/
{
	// traite les caractères spéciaux [\^$.|?*+()
	//remplace le carctère c par \c
	function escape(e)
	{
		a = new RegExp('\\'+e,'g');
		s = s.replace(a,'\\'+e);
	}

	chars = new Array('\\','[','^','$','.','|','?','*','+','(',')');
	//for(e in chars) escape(chars[e]); // compatibility issue with prototype
	for(var e=0; e<chars.length; e++) escape(chars[e]);
	return s;
}

function tf_CreateElm(tag)
/*====================================================
	- creates an html element with its attributes
	- accepts the following params:
		- a string defining the html tag
		to create
		- an undetermined # of arrays containing the
		couple 'attribute name','value' ['id','myId']
=====================================================*/
{
	if(tag==undefined || tag==null || tag=='') return;
	var el = document.createElement( tag );		
	if(arguments.length>1)
	{
		for(var i=0; i<arguments.length; i++)
		{
			var argtype = typeof arguments[i];
			switch( argtype.tf_LCase() )
			{
				case 'object':
					if( arguments[i].length==2 )
					{						
						el.setAttribute( arguments[i][0],arguments[i][1] );
					}//if array length==2
				break;
			}//switch
		}//for i
	}//if args
	return el;	
}

function tf_CreateText(node)
/*====================================================
	- this is just a document.createTextNode shortcut
=====================================================*/
{
	return document.createTextNode( node );
}

function tf_CreateOpt(text,value,isSel)
/*====================================================
	- creates an option element and returns it:
		- text: displayed text (string)
		- value: option value (string)
		- isSel: is selected option (boolean)
=====================================================*/
{
	var isSelected = isSel ? true : false;
	var opt = (isSelected) 
		? tf_CreateElm('option',['value',value],['selected','true'])
		: tf_CreateElm('option',['value',value]);
	opt.appendChild(tf_CreateText(text));
	return opt;
}

function tf_CreateCheckItem(chkIndex, chkValue, labelText)
/*====================================================
	- creates an checklist item and returns it
	- accepts the following params:
		- chkIndex: index of check item (number)
		- chkValue: check item value (string)
		- labelText: check item label text (string)
=====================================================*/
{
	if(chkIndex==undefined || chkValue==undefined || labelText==undefined )
		return;
	var li = tf_CreateElm('li');
	var label = tf_CreateElm('label',['for',chkIndex]);
	var check = tf_CreateElm( 'input',
					['id',chkIndex],
					['name',chkIndex],
					['type','checkbox'],
					['value',chkValue] );
	label.appendChild(check);
	label.appendChild(tf_CreateText(labelText));
	li.appendChild(label);
	li.label = label;
	li.check = check;
	return li;
}

function tf_HighlightWord( node,word,cssClass )
/*====================================================
	- highlights keyword found in passed node
	- accepts the following params:
		- node
		- word to search
		- css class name for highlighting
=====================================================*/
{
	// Iterate into this nodes childNodes
	if(node.hasChildNodes) 
		for( var i=0; i<node.childNodes.length; i++ )
			tf_HighlightWord(node.childNodes[i],word,cssClass);

	// And do this node itself
	if(node.nodeType == 3) 
	{ // text node
		var tempNodeVal = node.nodeValue.tf_LCase();
		var tempWordVal = word.tf_LCase();
		if(tempNodeVal.indexOf(tempWordVal) != -1) 
		{
			var pn = node.parentNode;
			if(pn.className != cssClass) 
			{
				// word has not already been highlighted!
				var nv = node.nodeValue;
				var ni = tempNodeVal.indexOf(tempWordVal);
				// Create a load of replacement nodes
				var before = tf_CreateText(nv.substr(0,ni));
				var docWordVal = nv.substr(ni,word.length);
				var after = tf_CreateText(nv.substr(ni+word.length));
				var hiwordtext = tf_CreateText(docWordVal);
				var hiword = tf_CreateElm('span');
				hiword.className = cssClass;
				hiword.appendChild(hiwordtext);
				pn.insertBefore(before,node);
				pn.insertBefore(hiword,node);
				pn.insertBefore(after,node);
				pn.removeChild(node);
			}
		}
	}// if node.nodeType == 3
}

function tf_UnhighlightWord( node,word,cssClass )
/*====================================================
	- removes highlights found in passed node
	- accepts the following params:
		- node
		- word to search
		- css class name for highlighting
=====================================================*/
{
	// Iterate into this nodes childNodes
	if(node.hasChildNodes)
		for( var i=0; i<node.childNodes.length; i++ )
			tf_UnhighlightWord(node.childNodes[i],word,cssClass);

	// And do this node itself
	if(node.nodeType == 3) 
	{ // text node
		var tempNodeVal = node.nodeValue.tf_LCase();
		var tempWordVal = word.tf_LCase();
		if(tempNodeVal.indexOf(tempWordVal) != -1)
		{
			var pn = node.parentNode;
			if(pn.className == cssClass)
			{
				var prevSib = pn.previousSibling;
				var nextSib = pn.nextSibling;
				nextSib.nodeValue = prevSib.nodeValue + node.nodeValue + nextSib.nodeValue;
				prevSib.nodeValue = '';
				node.nodeValue = '';
			}
		}
	}// if node.nodeType == 3
}

function tf_addEvent(obj,event_name,func_name)
{
	if (obj.attachEvent)
		obj.attachEvent('on'+event_name, func_name);
	else if(obj.addEventListener)
		obj.addEventListener(event_name,func_name,true);
	else
		obj['on'+event_name] = func_name;
}

function tf_removeEvent(obj,event_name,func_name)
{
	if (obj.detachEvent)
		obj.detachEvent('on'+event_name,func_name);
	else if(obj.removeEventListener)
		obj.removeEventListener(event_name,func_name,true);
	else
		obj['on'+event_name] = null;
}

function tf_NumSortAsc(a, b){ return (a-b); }

function tf_NumSortDesc(a, b){ return (b-a); }

function tf_IgnoreCaseSort(a, b) {
	var x = a.tf_LCase();
	var y = b.tf_LCase();
	return ((x < y) ? -1 : ((x > y) ? 1 : 0));
}

String.prototype.tf_MatchCase = function (mc) 
{
	if (!mc) return this.tf_LCase();
	else return this.toString();
}

String.prototype.tf_Trim = function()
{//optimised by Anthony Maes
	return this.replace(/(^[\s\xA0]*)|([\s\xA0]*$)/g,'');
}

String.prototype.tf_LCase = function()
{
	return this.toLowerCase();
}

String.prototype.tf_UCase = function()
{
	return this.toUpperCase();
}

Array.prototype.tf_Has = function(s,mc) 
{
	//return this.indexOf(s) >= 0;
	var sCase = (mc==undefined) ? false : mc;
	for (i=0; i<this.length; i++)
		if (this[i].toString().tf_MatchCase(sCase)==s) return true;
	return false;
}

Array.prototype.tf_IndexByValue = function(s,mc) 
{
	var sCase = (mc==undefined) ? false : mc;
	for (i=0; i<this.length; i++)
		if (this[i].toString().tf_MatchCase(sCase)==s) return i;
	return (-1);
}

// Is this IE 6? the ultimate browser sniffer ;-)
//window['tf_isIE'] = (window.innerHeight) ? false : true;
window['tf_isIE'] = (window.innerHeight) ? false : /msie|MSIE 6/.test(navigator.userAgent) ? true : false;
window['tf_isIE7'] = (window.innerHeight) ? false : /msie|MSIE 7/.test(navigator.userAgent) ? true : false;

function tf_hasClass(elm,cl) 
{
	return elm.className.match(new RegExp('(\\s|^)'+cl+'(\\s|$)'));
}

function tf_addClass(elm,cl) 
{
	if (!tf_hasClass(elm,cl))
		elm.className += ' '+cl;
}

function tf_removeClass(elm,cl) 
{
	if ( !tf_hasClass(elm,cl) ) return;
	var reg = new RegExp('(\\s|^)'+cl+'(\\s|$)');
	elm.className = elm.className.replace(reg,' ');
}

function tf_isValidDate(dateStr, format) 
{
	if (format == null) { format = 'DMY'; }
	format = format.toUpperCase();
	if (format.length != 3) { format = 'DMY'; }
	if ( (format.indexOf('M') == -1) || (format.indexOf('D') == -1) ||
		(format.indexOf('Y') == -1) ) { format = 'DMY'; }
	if (format.substring(0, 1) == 'Y') { // If the year is first
		  var reg1 = /^\d{2}(\-|\/|\.)\d{1,2}\1\d{1,2}$/;
		  var reg2 = /^\d{4}(\-|\/|\.)\d{1,2}\1\d{1,2}$/;
	} else if (format.substring(1, 2) == 'Y') { // If the year is second
		  var reg1 = /^\d{1,2}(\-|\/|\.)\d{2}\1\d{1,2}$/;
		  var reg2 = /^\d{1,2}(\-|\/|\.)\d{4}\1\d{1,2}$/;
	} else { // The year must be third
		  var reg1 = /^\d{1,2}(\-|\/|\.)\d{1,2}\1\d{2}$/;
		  var reg2 = /^\d{1,2}(\-|\/|\.)\d{1,2}\1\d{4}$/;
	}
	// If it doesn't conform to the right format (with either a 2 digit year or 4 digit year), fail
	if ( (reg1.test(dateStr) == false) && (reg2.test(dateStr) == false) ) { return false; }
	var parts = dateStr.split(RegExp.$1); // Split into 3 parts based on what the divider was
	// Check to see if the 3 parts end up making a valid date
	if (format.substring(0, 1) == 'M') { var mm = parts[0]; } else
		if (format.substring(1, 2) == 'M') { var mm = parts[1]; } else { var mm = parts[2]; }
	if (format.substring(0, 1) == 'D') { var dd = parts[0]; } else
		if (format.substring(1, 2) == 'D') { var dd = parts[1]; } else { var dd = parts[2]; }
	if (format.substring(0, 1) == 'Y') { var yy = parts[0]; } else
		if (format.substring(1, 2) == 'Y') { var yy = parts[1]; } else { var yy = parts[2]; }
	if (parseFloat(yy) <= 50) { yy = (parseFloat(yy) + 2000).toString(); }
	if (parseFloat(yy) <= 99) { yy = (parseFloat(yy) + 1900).toString(); }
	var dt = new Date(parseFloat(yy), parseFloat(mm)-1, parseFloat(dd), 0, 0, 0, 0);
	if (parseFloat(dd) != dt.getDate()) { return false; }
	if (parseFloat(mm)-1 != dt.getMonth()) { return false; }
	return true;
}

function tf_formatDate(dateStr, format)
{
	if(format==null) format = 'DMY';
	var oDate, parts;
	
	function y2kDate(yr){
		if(yr == undefined) return 0;
		if(yr.length>2) return yr;
		var y;
		if(yr <= 99 && yr>50) //>50 belong to 1900
			y = '19' + yr;
		if(yr<50 || yr =='00') //<50 belong to 2000
			y = '20' + yr;
		return y;
	}
	
	switch(format.toUpperCase())
	{
		case 'DMY':
			parts = dateStr.replace(/^(0?[1-9]|[12][0-9]|3[01])([- \/.])(0?[1-9]|1[012])([- \/.])((\d\d)?\d\d)$/,'$1 $3 $5').split(' ');
			oDate = new Date(y2kDate(parts[2]),parts[1]-1,parts[0]);
		break;
		case 'MDY':
			parts = dateStr.replace(/^(0?[1-9]|1[012])([- \/.])(0?[1-9]|[12][0-9]|3[01])([- \/.])((\d\d)?\d\d)$/,'$1 $3 $5').split(' ');
			oDate = new Date(y2kDate(parts[2]),parts[0]-1,parts[1]);
		break;
		case 'YMD':
			parts = dateStr.replace(/^((\d\d)?\d\d)([- \/.])(0?[1-9]|1[012])([- \/.])(0?[1-9]|[12][0-9]|3[01])$/,'$1 $4 $6').split(' ');
			oDate = new Date(y2kDate(parts[0]),parts[1]-1,parts[2]);
		break;
		default: //in case format is not correct
			parts = dateStr.replace(/^(0?[1-9]|[12][0-9]|3[01])([- \/.])(0?[1-9]|1[012])([- \/.])((\d\d)?\d\d)$/,'$1 $3 $5').split(' ');
			oDate = new Date(y2kDate(parts[2]),parts[1]-1,parts[0]);
		break;
	}
	return oDate;
}

function tf_removeNbFormat(data,format)
{
	if(data==null) return;
	if(format==null) format = 'us';
	var n = data;
	if( format.tf_LCase()=='us' )
		n =+ n.replace(/[^\d\.-]/g,'');
	else
		n =+ n.replace(/[^\d\,-]/g,'').replace(',','.');
	return n;
}

function tf_isImported(filePath,type)
{
	var isImported = false; 
	var importType = (type==undefined) ? 'script' : type;
	var files = tf_Tag(document,importType);
	for (var i=0; i<files.length; i++)
	{
		if(files[i].src == undefined) continue;
		if(files[i].src.match(filePath))
		{
			isImported = true;	
			break;
		}
	}
	return isImported;
}

function tf_WriteCookie(name, value, hours)
{
	var expire = '';
	if(hours != null)
	{
		expire = new Date((new Date()).getTime() + hours * 3600000);
		expire = '; expires=' + expire.toGMTString();
	}
	document.cookie = name + '=' + escape(value) + expire;
}

function tf_ReadCookie(name)
{
	var cookieValue = '';
	var search = name + '=';
	if(document.cookie.length > 0)
	{ 
		offset = document.cookie.indexOf(search);
		if (offset != -1)
		{ 
			offset += search.length;
			end = document.cookie.indexOf(';', offset);
			if (end == -1) end = document.cookie.length;
			cookieValue = unescape(document.cookie.substring(offset, end))
		}
	}
	return cookieValue;
}

function tf_CookieValueArray(name)
{
	var val = tf_ReadCookie(name); //reads the cookie
	var arr = val.split(','); //creates an array with filters' values
	return arr;
}

function tf_CookieValueByIndex(name, index)
{
	var val = tf_CookieValueArray(name); //reads the cookie
	return val[index];
}

function tf_RemoveCookie(name)
{
	tf_WriteCookie(name,'',-1);
}
/* --- */

/*====================================================
	- Backward compatibility fns
=====================================================*/
function grabEBI(id){ return tf_Id( id ); }
function grabTag(obj,tagname){ return tf_Tag(obj,tagname); }
function tf_GetCellText(n){ return tf_GetNodeText(n); }
function tf_isObject(varname){ return tf_isObj(varname); }
/* --- */
