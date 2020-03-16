<!DOCTYPE html>
<html lang="en">

	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
	    <meta name="csrf-token" content="{{ csrf_token() }}">

		<title>iReceptor Web Service</title>

		<!-- css -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
		<link href="/css/main.css?v=1" rel="stylesheet">

		<!-- IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>

	<body>

		<div class="container">
			<h1>
				<img src="/images/ireceptor_logo.png">
				iReceptor Repository
			</h1>

			<div class="row">
				<div class="col-md-6">
					@if (config('ireceptor.provenance_url') != '')
						<h2>Provenance</h2>
						<p>
							<a href="{{ config('ireceptor.provenance_url') }}">
								{{ config('ireceptor.provenance_url') }}
							</a>
						</p>
					@endif

					<h2>ADC API</h2>
					<p> The iReceptor Web Service implements the AIRR Data Commons Web API to search repositories that are compliant with the AIRR Community standards. This web service is the entry point to searching the AIRR-seq data stored in this repository. For more information on searching this repository, please refer to the <a href="https://docs.airr-community.org/en/latest/api/adc_api.html">AIRR Data Commons API documentation</a></p>
					<ul>
						<li><a href="/airr/v1/info">/airr/v1/info</a></li>

						<li><a href="https://docs.airr-community.org/en/latest/datarep/metadata.html">AIRR Repertoire Schema</a>, an abstract organizational unit of analysis that is defined by the researcher and consists of study metadata, subject metadata, sample metadata, cell processing metadata, nucleic acid processing metadata, sequencing run metadata, a set of raw sequence files, data processing metadata, and a set of Rearrangements.</li>
						<li><a href="https://docs.airr-community.org/en/latest/datarep/rearrangements.html">AIRR Rearrangement Schema</a>, a sequence which describes a rearranged adaptive immune receptor chain (e.g., antibody heavy chain or TCR beta chain) along with a host of annotations.</li>
						
					</ul>
					
					<h2>About iReceptor</h2>
					<p>iReceptor federates Adaptive Immune Receptor Repertoire (AIRR-seq) data repositories from multiple laboratories and enable researchers to easily and efficiently perform complex analyses on these federated repositories via the <a href="https://gateway.ireceptor.org">iRececeptor Gateway</a>.</p>
					<p>For more information, visit the <a href="https://ireceptor.org">iReceptor website</a>.</p>

				</div>
			</div>
	</body>
</html>