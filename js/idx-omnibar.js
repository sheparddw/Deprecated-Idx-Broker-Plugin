		var idxOmnibar = function(jsonData){
			//prevent script from running twice or erroring if no omnibar
		if(document.querySelector('.idx-omnibar-input') && !document.querySelector('.awesomplete')){
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
			var createArrays = function(array, newArray, zip){
				//if zip, do not append state abbreviations
				if(zip){
					array.forEach(function(item){newArray.push(item.name);});
				} else {
					array.forEach(function(item){newArray.push(item.name + ', ' + item.stateAbrv);});
				}
				return newArray;
			};

			//dependent upon createArrays. Creates cczList array
			var buildLocationList = function (data){
				return createArrays(data.zipcodes, createArrays(data.counties, createArrays(data.cities, cczList)), true);
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
				new Awesomplete(value,{autoFirst: true}).list = removeDuplicates(buildLocationList(jsonData));
			});


		/*
		* Running the Search
		*/
		var foundResult = false;

		var goToResultsPage = function (input, url, additionalquery, listingID){
			if(listingID !== undefined){
				return window.location = url + additionalquery;
			}
			return window.location = url + additionalquery + setExtraFieldValues(input);
		};


		var checkExtraFieldValues = function(input, fieldType, resultsQuery){
			if(input.parentNode.parentNode.querySelector('.idx-omnibar-'+fieldType).value){
				return resultsQuery + input.parentNode.parentNode.querySelector('.idx-omnibar-'+fieldType).value;
			} else {
				return '';
			}
		};

		var setExtraFieldValues = function(input){
			var extraValues = '';
			extraValues += checkExtraFieldValues(input, 'price', '&hp=');
			extraValues += checkExtraFieldValues(input, 'bed', '&bd=');
			extraValues += checkExtraFieldValues(input, 'bath', '&tb=');
			return extraValues;
		};

		var whatState = function(inputState, idxState){
			var availableStates = [{
				    "name": "Alabama",
				        "abbreviation": "AL"
				}, {
				    "name": "Alaska",
				        "abbreviation": "AK"
				}, {
					"name": "Alberta",
						"abbreviation": "AB"
				}, {
				    "name": "American Samoa",
				        "abbreviation": "AS"
				}, {
				    "name": "Arizona",
				        "abbreviation": "AZ"
				}, {
				    "name": "Arkansas",
				        "abbreviation": "AR"
				}, {
					"name": "Baha California",
						"abbreviation": "BC"
				}, {
					"name": "Bahamas",
						"abbreviation": "BS"
				}, {
				    "name": "British Columbia",
				        "abbreviation": "BC"
				}, {
				    "name": "California",
				        "abbreviation": "CA"
				}, {
				    "name": "Colorado",
				        "abbreviation": "CO"
				}, {
				    "name": "Connecticut",
				        "abbreviation": "CT"
				}, {
				    "name": "Delaware",
				        "abbreviation": "DE"
				}, {
				    "name": "District Of Columbia",
				        "abbreviation": "DC"
				}, {
				    "name": "Federated States Of Micronesia",
				        "abbreviation": "FM"
				}, {
				    "name": "Florida",
				        "abbreviation": "FL"
				}, {
				    "name": "Georgia",
				        "abbreviation": "GA"
				}, {
				    "name": "Guam",
				        "abbreviation": "GU"
				}, {
				    "name": "Hawaii",
				        "abbreviation": "HI"
				}, {
				    "name": "Idaho",
				        "abbreviation": "ID"
				}, {
				    "name": "Illinois",
				        "abbreviation": "IL"
				}, {
				    "name": "Indiana",
				        "abbreviation": "IN"
				}, {
				    "name": "Iowa",
				        "abbreviation": "IA"
				}, {
					"name": "Jamaica",
						"abbreviation": "JM"
				}, {
				    "name": "Kansas",
				        "abbreviation": "KS"
				}, {
				    "name": "Kentucky",
				        "abbreviation": "KY"
				}, {
				    "name": "Louisiana",
				        "abbreviation": "LA"
				}, {
				    "name": "Maine",
				        "abbreviation": "ME"
				}, {
				    "name": "Manitoba",
				        "abbreviation": "MB"
				}, {
				    "name": "Marshall Islands",
				        "abbreviation": "MH"
				}, {
				    "name": "Maryland",
				        "abbreviation": "MD"
				}, {
				    "name": "Massachusetts",
				        "abbreviation": "MA"
				}, {
					"name": "Mexico",
						"abbreviation": "MX"
				}, {
				    "name": "Michigan",
				        "abbreviation": "MI"
				}, {
				    "name": "Minnesota",
				        "abbreviation": "MN"
				}, {
				    "name": "Mississippi",
				        "abbreviation": "MS"
				}, {
				    "name": "Missouri",
				        "abbreviation": "MO"
				}, {
				    "name": "Montana",
				        "abbreviation": "MT"
				}, {
				    "name": "Nebraska",
				        "abbreviation": "NE"
				}, {
				    "name": "Nevada",
				        "abbreviation": "NV"
				}, {
				    "name": "New Brunswick",
				        "abbreviation": "NB"
				}, {
				    "name": "New Hampshire",
				        "abbreviation": "NH"
				}, {
				    "name": "New Jersey",
				        "abbreviation": "NJ"
				}, {
				    "name": "New Mexico",
				        "abbreviation": "NM"
				}, {
				    "name": "New York",
				        "abbreviation": "NY"
				}, {
				    "name": "Newfoundland and Labrador",
				        "abbreviation": "NL"
				}, {
				    "name": "North Carolina",
				        "abbreviation": "NC"
				}, {
				    "name": "North Dakota",
				        "abbreviation": "ND"
				}, {
				    "name": "Northern Mariana Islands",
				        "abbreviation": "MP"
				}, {
				    "name": "Nova Scotia",
				        "abbreviation": "NS"
				}, {
				    "name": "Northwest Territories",
				        "abbreviation": "NT"
				}, {
				    "name": "Nunavut",
				        "abbreviation": "NU"
				}, {
				    "name": "Ohio",
				        "abbreviation": "OH"
				}, {
				    "name": "Oklahoma",
				        "abbreviation": "OK"
				}, {
				    "name": "Ontario",
				        "abbreviation": "ON"
				}, {
				    "name": "Oregon",
				        "abbreviation": "OR"
				}, {
				    "name": "Palau",
				        "abbreviation": "PW"
				}, {
				    "name": "Pennsylvania",
				        "abbreviation": "PA"
				}, {
				    "name": "Prince Edward Island",
				        "abbreviation": "PE"
				}, {
				    "name": "Puerto Rico",
				        "abbreviation": "PR"
				}, {
				    "name": "Quebec",
				        "abbreviation": "QC"
				}, {
				    "name": "Rhode Island",
				        "abbreviation": "RI"
				}, {
				    "name": "Saskatchewan",
				        "abbreviation": "SK"
				}, {
				    "name": "South Carolina",
				        "abbreviation": "SC"
				}, {
				    "name": "South Dakota",
				        "abbreviation": "SD"
				}, {
				    "name": "Tennessee",
				        "abbreviation": "TN"
				}, {
				    "name": "Texas",
				        "abbreviation": "TX"
				}, {
				    "name": "Utah",
				        "abbreviation": "UT"
				}, {
				    "name": "Vermont",
				        "abbreviation": "VT"
				}, {
				    "name": "Virgin Islands",
				        "abbreviation": "VI"
				}, {
				    "name": "Virginia",
				        "abbreviation": "VA"
				}, {
				    "name": "Washington",
				        "abbreviation": "WA"
				}, {
				    "name": "West Virginia",
				        "abbreviation": "WV"
				}, {
				    "name": "Wisconsin",
				        "abbreviation": "WI"
				}, {
				    "name": "Wyoming",
				        "abbreviation": "WY"
				}, {
				    "name": "Yukon",
				        "abbreviation": "YT"
				}]
			if(inputState === undefined){
				return true;
			}
			for(var i = 0; i < availableStates.length; i++){
				if(availableStates[i].abbreviation === inputState.toUpperCase() || availableStates[i].name.toUpperCase() === inputState.toUpperCase()){
					if(availableStates[i].abbreviation === idxState){
						return true;
					}
				}
			}

		}
		//checks against the cities, counties, and zipcodes. If no match, runs callback
		var checkAgainstList = function (input, list, listType, callback){
			for(var i=0; i < list.length; i++){
				if (input.value.split(', ')[0].toLowerCase() === list[i].name.toLowerCase() && whatState(input.value.split(', ')[1], list[i].stateAbrv)) {
					switch(listType){
						case 'cities':
							foundResult = true;
							goToResultsPage(input, idxUrl, '?ccz=city&city[]=' + list[i].id);
							break;
						case 'counties':
							foundResult = true;
							goToResultsPage(input, idxUrl, '?ccz=county&county[]=' + list[i].id);
							break;
						case 'zipcodes':
							foundResult = true;
							goToResultsPage(input, idxUrl, '?ccz=zipcode&zipcode[]=' + list[i].id);
							break;
					}
				} else if (foundResult === false && i == list.length - 1) {
					return callback;
				}
			}
		};

		//callback for checkAgainstList function. Inherits global idxUrl variable from widget HTML script
		var notOnList = function (input) {
				var hasSpaces = /\s/g.test(input.value);
				if (!input.value) {
					//nothing in input
					goToResultsPage(input, idxUrl, '?pt=all');
				} else if(hasSpaces === false && parseInt(input.value) !== isNaN) {
					//MLS Number/ListingID
					var listingID = true;
					goToResultsPage(input, idxUrl, '?csv_listingID=' + input.value, listingID);
				} else {
					//address (split into number and street)
					var addressSplit = input.value.split(' ');
					//if first entry is number, search for street number otherwise search for street name
					if(Number(addressSplit[0]) > 0){
						goToResultsPage(input, idxUrl, '?a_streetNumber=' + addressSplit[0] + '&aw_streetName=' + addressSplit[1]);
					} else if(addressSplit[0] === 'City,'){
						//prevent placeholder from interfering with results URL
						goToResultsPage(input, idxUrl, '?pt=all');
					} else {
						goToResultsPage(input, idxUrl, '?aw_streetName=' + addressSplit[0]);
					}
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
			//make omnibar fit sidebars better by pushing button to bottom for basic omnibr
			forEach(document.querySelectorAll('.idx-omnibar-original-form'), function(index, value){
				if(value.offsetWidth < 400){
					value.classList.add('idx-omnibar-mini');
				}
			})


		}
	};
