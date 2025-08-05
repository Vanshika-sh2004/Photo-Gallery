<?php
include "db.php";

$id = $_GET['id'] ?? 0;
$event = $conn->query("SELECT * FROM events WHERE id=$id")->fetch_assoc();

if (!$event) {
    die("Event not found.");
}

$folder = "uploads/" . $event['financial_year'] . "/" . $event['folder_name'];
if (!is_dir($folder)) {
    die("Upload folder not found.");
}

$media = array_values(array_filter(scandir($folder), fn($f) =>
    !in_array($f, ['.', '..']) && preg_match('/\.(jpe?g|png|mp4|wmv)$/i', $f)
));
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($event['name']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
    :root {
        --navy: #001f4d;
        --turquoise: rgb(112, 124, 255);
        --magenta: rgb(4, 218, 222);
        --babypink: #ffc0cb;
        --yellow: #f0e68c;
        --white: #ffffff;
    }

    body {
        margin: 0;
        font-family: 'Segoe UI', sans-serif;
        color: var(--white);
        overflow-x: hidden;
        background: linear-gradient(-45deg, var(--navy), var(--turquoise), var(--magenta), var(--babypink), var(--yellow));
        background-size: 500% 500%;
        animation: waveBG 12s ease infinite;
        position: relative;
        z-index: 1;
    }

    @keyframes waveBG {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    h2 {
        font-size: 2rem;
        font-weight: 600;
        text-align: center;
        background: var(--yellow);
        padding: 15px 25px;
        margin-bottom: 40px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        color: black;
        width: fit-content;
        margin-left: auto;
        margin-right: auto;
        border-radius: 10px;
        position: relative;
        z-index: 2;
    }

    .media-wrapper {
        position: relative;
        width: 100%;
        height: 200px;
        overflow: hidden;
        background-color: black;
        border-radius: 10px;
        transition: transform 0.6s ease;
        opacity: 0;
        transform: translateX(-100px);
        animation: slideIn 0.8s ease forwards;
    }

    @keyframes slideIn {
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .col-md-3:nth-child(1) .media-wrapper { animation-delay: 0s; }
    .col-md-3:nth-child(2) .media-wrapper { animation-delay: 0.1s; }
    .col-md-3:nth-child(3) .media-wrapper { animation-delay: 0.2s; }
    .col-md-3:nth-child(4) .media-wrapper { animation-delay: 0.3s; }
    .col-md-3:nth-child(5) .media-wrapper { animation-delay: 0.4s; }
    .col-md-3:nth-child(6) .media-wrapper { animation-delay: 0.5s; }

    .media-wrapper img,
    .media-wrapper video {
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 10px;
        transition: object-fit 0.6s ease, transform 0.6s ease;
    }

    .media-wrapper:hover img,
    .media-wrapper:hover video {
        object-fit: cover;
        transform: scale(1.05);
    }

    a {
        text-decoration: none;
    }

    @media (max-width: 768px) {
        .media-wrapper {
            height: 160px;
        }
        h2 {
            font-size: 1.5rem;
        }
    }

    .bubbles {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        pointer-events: none;
        z-index: 0;
    }

    .bubble {
        position: absolute;
        bottom: -60px;
        border-radius: 50%;
        animation: rise infinite ease-in;
        box-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
    }

    @keyframes rise {
        0% {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
        100% {
            transform: translateY(-120vh) scale(0.5);
            opacity: 0;
        }
    }
    </style>
</head>
<body>

<div class="bubbles">
<?php
for ($i = 0; $i < 30; $i++) {
    $left = rand(0, 100);
    $size = rand(10, 40);
    $delay = rand(0, 20);
    $duration = rand(10, 25);
    $colors = ['255,255,255', '255,182,193', '135,206,250', '144,238,144'];
    $color = $colors[array_rand($colors)];
    echo "<div class='bubble' style='
        left: {$left}%;
        width: {$size}px;
        height: {$size}px;
        animation-delay: -{$delay}s;
        animation-duration: {$duration}s;
        background: rgba({$color}, 0.6);
    '></div>";
}
?>
</div>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-start flex-wrap mb-4">
    <div class="flex-grow-1 text-center">
        <h2 class="event-heading">
            इवेंट / Event: <?php echo htmlspecialchars($event['name']) . " (" . htmlspecialchars($event['financial_year']) . ")"; ?>
        </h2>
    </div>
   <a href="index.php" class="btn btn-success ms-auto mt-4">
    🖼️ Back to Gallery
</a>

</div>

    <div class="row">
        <?php foreach ($media as $file):
            $path = $folder . '/' . $file;
            $isImage = preg_match('/\.(jpe?g|png)$/i', $file);
        ?>
        <div class="col-md-3 col-sm-6 mb-4">
            <a href="carousel.php?id=<?php echo urlencode($id); ?>&file=<?php echo urlencode($file); ?>">
                <div class="media-wrapper">
                    <?php if ($isImage): ?>
                        <img src="<?php echo $path; ?>" alt="">
                    <?php else: ?>
                        <video src="<?php echo $path; ?>" muted></video>
                    <?php endif; ?>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
