<?php

return [
	'title' => [
		'welcome'       => ":site_name wita!",
		'statistics'    => "Statystyki strony",

		'featured_post' => "Promowany post",
		'recent_images' => "Ostatnie obrazki",
		'recent_posts'  => "Ostatnie posty",
	],

	'info' => [
		'welcome' => "<p>Ta strona używa <a href=\"https://github.com/infinity-next/infinity-next\">Infinity Next</a>, " .
			"oprogramowania do tworzenia for obrazkowych opartego na <a href=\"https://laravel.com\">frameworku Laravel</a>. " .
			"Infinity Next jest wydany na licencji AGPL 3.0, co oznacza, że każdy może ściągnąć i zainstalować instancję tego silnika samemu.</p>" .
			"<p>Board <a href=\"/test/\">/test/</a> został utworzony.</p>",

		'statistic' => [
			'boards' => "Jest tylko jeden board.|Obecnie jest :boards_public publicznych i :boards_total razem.",
			'posts'  => "Wczoraj na całej stronie zostało utworzonych :recent_posts.",
			'posts_all' => "Od :start_date zostało utworzonych :posts_total",

			// Inserted into the above values. A post/board count is wrapped here, then concatenated above.
			'post_count' => "<strong>:posts</strong> post|<strong>:posts</strong> posty|<strong>:posts</strong> postów",
			'board_count' => "<strong>:boards</strong> board|<strong>:boards</strong> boardy|<strong>:boards</strong> boardów",
		],
	]
];
