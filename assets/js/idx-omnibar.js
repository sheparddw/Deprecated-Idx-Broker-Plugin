	function idxOmnibar(jsonData){
		if(document.querySelector('.idx-omnibar-input')){

			/*
			* Autocomplete
			*/

			var cczList = [];

			//helper function runs function for each item in DOM array
			var forEach = function (array, callback, scope) {
			  for (var i = 0; i < array.length; i++) {
			    callback.call(scope, i, array[i]);
			  }
			};

			//helper function for grabbing the name of each item in JSON creating new array
			var createArrays = function(array, newArray){
				array.forEach(function(item){newArray.push(item.name);});
				return newArray;
			};

			//dependent upon createArrays. Creates cczList array
			var buildLocationList = function (data){
				return createArrays(data.zipcodes, createArrays(data.counties, createArrays(data.cities, cczList)));
			};

			var removeDuplicates = function(data) {
					var seen = {};
					var out = [];
					var len = data.length;
					var j = 0;
					for(var i = 0; i < len; i++) {
							var item = data[i];
							if(seen[item] !== 1) {
										seen[item] = 1;
										out[j++] = item;
							}
					}
					return out;
			};



			//Initialize Autocomplete of CCZs for each omnibar allowing multiple per page
			forEach(document.querySelectorAll('.idx-omnibar-input'), function (index, value) {
				new Awesomplete(value).list = removeDuplicates(buildLocationList(jsonData));
			});


		/*
		* Running the Search
		*/
		var foundResult = false;

		var goToResultsPage = function (url, additionalquery){
			return window.location = url + additionalquery;
		};

		//checks against the cities, counties, and zipcodes. If no match, runs callback
		var checkAgainstList = function (input, list, listType, callback){
			for(var i=0; i < list.length; i++){
				if (input.value.toLowerCase() == list[i].name.toLowerCase()) {
					switch(listType){
						case 'cities':
							foundResult = true;
							goToResultsPage(idxUrl, '?ccz=city&city[]=' + jsonData.cities[i].id);
							break;
						case 'counties':
							foundResult = true;
							goToResultsPage(idxUrl, '?ccz=county&county[]=' + jsonData.counties[i].id);
							break;
						case 'zipcodes':
							foundResult = true;
							goToResultsPage(idxUrl, '?ccz=zipcode&zipcode[]=' + jsonData.zipcodes[i].id);
							break;
					}
				} else if (foundResult === false && i == list.length - 1) {
					callback;
				}
			}
		};

		//callback for checkAgainstList function. Inherits global idxUrl variable from widget HTML script
		var notOnList = function (input) {
				var hasSpaces = /\s/g.test(input);
				if (!input) {
					//nothing in input
					goToResultsPage(idxUrl, '');
				} else if(hasSpaces === false && parseInt(input) !== isNaN) {
					//MLS Number/ListingID
					goToResultsPage(idxUrl, '?csv_listingID=' + input);
				} else {
					//address (split into number and street)
					var addressSplit = input.split(' ');
					goToResultsPage(idxUrl, '?a_streetNumber=' + addressSplit[0] + '&aw_streetName=' + addressSplit[1]);
				}
			};

			var runSearch = function(event) {
				event.preventDefault();
				var input = event.target.querySelector('.idx-omnibar-input');
				checkAgainstList(input, jsonData.zipcodes, 'zipcodes', checkAgainstList(input, jsonData.counties, 'counties', checkAgainstList(input, jsonData.cities, 'cities')));
				if(foundResult === false){
					notOnList(input);
				}
			};

			//on submit, run the search (applies this to each omnibar)
			forEach(document.querySelectorAll('.idx-omnibar-form'), function(index, value){value.addEventListener('submit', runSearch);});

		}
	}
