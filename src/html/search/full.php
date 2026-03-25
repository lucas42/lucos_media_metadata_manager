<?php
require("../../authentication.php");
?>
<!DOCTYPE html>
<html>

<head>
	<title>Lucos Media Metadata Manager - Search</title>
	<link href="/style.css" rel="stylesheet">
	<link rel="icon" href="/icon" />
	<link rel="manifest" href="/manifest.json" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="mobile-web-app-capable" content="yes">
	<script type="text/javascript">
		const arachneUrl = "<?= htmlspecialchars(getenv('ARACHNE_URL'))?>";
		const arachneKey = "<?= htmlspecialchars(getenv('KEY_LUCOS_ARACHNE'))?>";
	</script>
</head>

<body>
	<lucos-navbar bg-colour="#000020">Metadata Manager</lucos-navbar>
	<a href="/" class="mock-button nav-home">&lt;- Home </a>
	<div id="content">
		<h2>Search Tracks</h2>
		<div class="form-field">
			<label for="search-input" class="key-label medium-key">Search Term</label>
			<span class="form-input">
				<input type="text" id="search-input" autofocus />
			</span>
		</div>
		<div id="facet-filters"></div>
		<div id="search-error" style="display:none;">
			<small>Search is currently unavailable. <a href="/search">Try the advanced search instead.</a></small>
		</div>
		<ul id="results"></ul>
		<div class="pagination" id="pagination"></div>
		<a href="/search" class="standalone">Advanced Search / Bulk Edit</a>
	</div>
	<script src="/script.js" type="text/javascript"></script>
	<script type="text/javascript">
	(function() {
		const searchInput = document.getElementById('search-input');
		const resultsList = document.getElementById('results');
		const paginationDiv = document.getElementById('pagination');
		const errorDiv = document.getElementById('search-error');
		const facetFiltersDiv = document.getElementById('facet-filters');
		let debounceTimer = null;
		let currentPage = 1;
		let currentQuery = '';
		let activeFacets = {};

		// Sanitise a Typesense highlight snippet: escape all HTML, then re-allow only <mark> tags
		function sanitiseSnippet(snippet) {
			const div = document.createElement('div');
			div.textContent = snippet;
			return div.innerHTML.replace(/&lt;mark&gt;/g, '<mark>').replace(/&lt;\/mark&gt;/g, '</mark>');
		}

		// Extract track ID from the Typesense document id (a URI like https://host/tracks/123)
		function extractTrackId(uri) {
			const match = uri.match(/\/tracks\/(\d+)$/);
			return match ? match[1] : null;
		}

		function buildSearchUrl(query, page) {
			const params = new URLSearchParams({
				q: query || '*',
				query_by: 'title,artist,album,genre,composer,lyrics,comment',
				per_page: '20',
				page: String(page),
				facet_by: 'artist,album,genre,language,year',
			});

			// Apply active facet filters
			const filterParts = [];
			for (const [field, values] of Object.entries(activeFacets)) {
				if (values.length > 0) {
					const escaped = values.map(v => '`' + v.replace(/`/g, '\\`') + '`');
					filterParts.push(field + ':=[' + escaped.join(',') + ']');
				}
			}
			if (filterParts.length > 0) {
				params.set('filter_by', filterParts.join(' && '));
			}

			return arachneUrl + '/search/tracks?' + params.toString();
		}

		function renderResults(data) {
			resultsList.innerHTML = '';
			errorDiv.style.display = 'none';

			if (!data.hits || data.hits.length === 0) {
				resultsList.innerHTML = '<li>No results found.</li>';
				paginationDiv.innerHTML = '';
				renderFacets(data.facet_counts || []);
				return;
			}

			data.hits.forEach(function(hit) {
				const doc = hit.document;
				const trackId = extractTrackId(doc.id);
				if (!trackId) return;

				const li = document.createElement('li');
				const h3 = document.createElement('h3');
				const a = document.createElement('a');
				a.href = '/tracks/' + trackId;

				// Build display title: "Artist - Title" or just "Title"
				// Check for highlighted text from Typesense (contains <mark> tags, other HTML is escaped)
				let hasHighlight = false;
				let displayTitle = doc.title;
				if (doc.artist && doc.artist.length > 0) {
					displayTitle = doc.artist.join(', ') + ' - ' + displayTitle;
				}

				if (hit.highlights) {
					const titleHighlight = hit.highlights.find(h => h.field === 'title');
					const artistHighlight = hit.highlights.find(h => h.field === 'artist');
					if (titleHighlight && titleHighlight.snippet) {
						displayTitle = (doc.artist && doc.artist.length > 0 ? doc.artist.join(', ') + ' - ' : '') + titleHighlight.snippet;
						hasHighlight = true;
					}
					if (artistHighlight && artistHighlight.snippet) {
						displayTitle = artistHighlight.snippet + ' - ' + doc.title;
						hasHighlight = true;
					}
				}

				// Use innerHTML only for Typesense highlights, with sanitisation to allow only <mark> tags
				if (hasHighlight) {
					a.innerHTML = sanitiseSnippet(displayTitle);
				} else {
					a.textContent = displayTitle;
				}
				h3.appendChild(a);
				li.appendChild(h3);

				// Show metadata chips
				const meta = document.createElement('div');
				meta.className = 'result-meta';
				if (doc.album && doc.album.length > 0) {
					meta.appendChild(createChip('album', doc.album.join(', ')));
				}
				if (doc.genre && doc.genre.length > 0) {
					meta.appendChild(createChip('genre', doc.genre.join(', ')));
				}
				if (doc.year) {
					meta.appendChild(createChip('year', doc.year));
				}
				if (meta.children.length > 0) {
					li.appendChild(meta);
				}

				resultsList.appendChild(li);
			});

			renderPagination(data.found, data.page, Math.ceil(data.found / data.request_params.per_page));
			renderFacets(data.facet_counts || []);
		}

		function createChip(label, value) {
			const span = document.createElement('span');
			span.className = 'result-chip';
			span.textContent = label + ': ' + value;
			return span;
		}

		function renderPagination(totalFound, page, totalPages) {
			paginationDiv.innerHTML = '';
			if (totalPages <= 1) return;

			if (page > 1) {
				const prev = document.createElement('a');
				prev.href = '#';
				prev.textContent = '<- Prev';
				prev.addEventListener('click', function(e) {
					e.preventDefault();
					currentPage = page - 1;
					doSearch();
				});
				paginationDiv.appendChild(prev);
				paginationDiv.appendChild(document.createTextNode(' | '));
			}

			paginationDiv.appendChild(document.createTextNode('Page ' + page + ' of ' + totalPages));

			if (page < totalPages) {
				paginationDiv.appendChild(document.createTextNode(' | '));
				const next = document.createElement('a');
				next.href = '#';
				next.textContent = 'Next ->';
				next.addEventListener('click', function(e) {
					e.preventDefault();
					currentPage = page + 1;
					doSearch();
				});
				paginationDiv.appendChild(next);
			}
		}

		function renderFacets(facetCounts) {
			facetFiltersDiv.innerHTML = '';
			if (!facetCounts || facetCounts.length === 0) return;

			facetCounts.forEach(function(facet) {
				if (!facet.counts || facet.counts.length === 0) return;

				const wrapper = document.createElement('div');
				wrapper.className = 'facet-group';

				const label = document.createElement('span');
				label.className = 'facet-label';
				label.textContent = facet.field_name + ': ';
				wrapper.appendChild(label);

				const activeValues = activeFacets[facet.field_name] || [];

				facet.counts.slice(0, 5).forEach(function(count) {
					const btn = document.createElement('button');
					btn.className = 'facet-btn' + (activeValues.includes(count.value) ? ' active' : '');
					btn.textContent = count.value + ' (' + count.count + ')';
					btn.addEventListener('click', function() {
						toggleFacet(facet.field_name, count.value);
					});
					wrapper.appendChild(btn);
				});

				facetFiltersDiv.appendChild(wrapper);
			});
		}

		function toggleFacet(field, value) {
			if (!activeFacets[field]) {
				activeFacets[field] = [];
			}
			const idx = activeFacets[field].indexOf(value);
			if (idx >= 0) {
				activeFacets[field].splice(idx, 1);
				if (activeFacets[field].length === 0) {
					delete activeFacets[field];
				}
			} else {
				activeFacets[field].push(value);
			}
			currentPage = 1;
			doSearch();
		}

		function doSearch() {
			const query = searchInput.value.trim();
			currentQuery = query;

			if (!query && Object.keys(activeFacets).length === 0) {
				resultsList.innerHTML = '';
				paginationDiv.innerHTML = '';
				facetFiltersDiv.innerHTML = '';
				errorDiv.style.display = 'none';
				return;
			}

			const url = buildSearchUrl(query, currentPage);

			fetch(url, {
				headers: {
					'X-TYPESENSE-API-KEY': arachneKey,
				}
			})
			.then(function(response) {
				if (!response.ok) throw new Error('Search request failed');
				return response.json();
			})
			.then(function(data) {
				renderResults(data);
			})
			.catch(function(err) {
				console.error('Search error:', err);
				resultsList.innerHTML = '';
				paginationDiv.innerHTML = '';
				errorDiv.style.display = 'block';
			});
		}

		searchInput.addEventListener('input', function() {
			clearTimeout(debounceTimer);
			currentPage = 1;
			debounceTimer = setTimeout(doSearch, 300);
		});

		// Handle initial query from URL parameter
		const urlParams = new URLSearchParams(window.location.search);
		const initialQuery = urlParams.get('q');
		if (initialQuery) {
			searchInput.value = initialQuery;
			doSearch();
		}
	})();
	</script>
</body>

</html>
