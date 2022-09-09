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
		<link href="/css/main.css?v=2" rel="stylesheet">

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
				{{ config('ireceptor.airr_info_title') }}
				<em>iReceptor Repository</em>
			</h1>

			<div class="row">

				<div class="col-md-6">
					<p>
						This AIRR-seq data repository can be searched using the <a href="https://docs.airr-community.org/en/latest/api/adc_api.html">AIRR Data Commons API</a>. <br />
						<strong>API version: {{ config('ireceptor.airr_info_api_version') }}</strong>
					</p>

					<p>For example, to get the list of repertoires using <code>curl</code>:</p>
					<pre><code>curl -k --data "{}" "https://this_repository_URL/airr/v1/repertoire"</code></pre>

					<p>For more information about this repository's API, see the <a href="/airr/v1/info">/info</a> entry point.</p>

					<h2>About the data</h2>
					<ul>
						<li><a href="https://docs.airr-community.org/en/latest/datarep/metadata.html">AIRR repertoires</a>: abstract organizational units of analysis defined by the researcher, consisting of study metadata, subject metadata, sample metadata, cell processing metadata, nucleic acid processing metadata, sequencing run metadata, a set of raw sequence files, data processing metadata, and a set of AIRR Rearrangements.</li>
						<li><a href="https://docs.airr-community.org/en/latest/datarep/rearrangements.html">AIRR rearrangements</a>: sequences describing a rearranged adaptive immune receptor chain (e.g., antibody heavy chain or TCR beta chain), along with a host of annotations.</li>
					</ul>
				</div>
				<div class="col-md-1"></div>
				<div class="col-md-4 sidebar">
					<h2>Contact</h2>
						<p>
							<span class="glyphicon glyphicon-globe" aria-hidden="true"></span>  Website: <a href="{{ config('ireceptor.airr_info_contact_url') }}" class="external" target="_blank">{{ config('ireceptor.airr_info_contact_url') }}</a><br />
							<span class="glyphicon glyphicon-envelope" aria-hidden="true"></span> Email: <a href="mailto:{{ config('ireceptor.airr_info_contact_email') }}">{{ config('ireceptor.airr_info_contact_email') }}</a>
						</p>
					<h2>About iReceptor</h2>
					<p>iReceptor federates Adaptive Immune Receptor Repertoire (AIRR-seq) data repositories from multiple laboratories and enable researchers to easily and efficiently perform complex analyses on these federated repositories via the <a href="https://gateway.ireceptor.org">iRececeptor Gateway</a>.</p>
					<p>This repository is an <a href="https://github.com/sfu-ireceptor/turnkey-service-php/">iReceptor Turnkey</a>, a quick and easy solution for researchers to create their own AIRR Data Commons repository.</p>
					<p>For more information, visit the <a href="https://ireceptor.org">iReceptor website</a>.</p>
				</div>

			</div>

	</body>
</html>