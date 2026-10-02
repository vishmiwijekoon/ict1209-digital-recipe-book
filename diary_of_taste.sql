-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 06, 2026 at 08:39 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `diary_of_taste`
--

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `name`, `email`, `message`, `created_at`) VALUES
(1, 'Kasun Perera', 'kasun@example.com', 'Loved the Vegetable Fried Rice recipe! Could you please share more quick 15-minute dinner ideas?', '2026-09-06 05:55:16'),
(2, 'Amaya Silva', 'amaya@example.com', 'Hello! Are there dairy-free alternatives available for the Salted Caramel Brownies recipe?', '2026-09-06 05:55:16');

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `ingredients` text NOT NULL,
  `instructions` text NOT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'Dinner',
  `image_url` varchar(500) DEFAULT NULL,
  `prep_time` varchar(50) DEFAULT '15 Min',
  `cook_time` varchar(50) DEFAULT '20 Min',
  `servings` varchar(50) DEFAULT '4',
  `difficulty` varchar(50) DEFAULT 'Easy',
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`id`, `title`, `description`, `ingredients`, `instructions`, `category`, `image_url`, `prep_time`, `cook_time`, `servings`, `difficulty`, `user_id`, `created_at`) VALUES
(1, 'Golden Buttermilk Pancakes', 'Fluffy stacks with a crisp edge, finished with warm maple syrup and toasted butter.', '2 cups all-purpose flour\r\n2 tbsp sugar\r\n2 tsp baking powder\r\n1/2 tsp salt\r\n2 large eggs\r\n1 3/4 cups buttermilk\r\n1/4 cup melted butter\r\n1 tsp vanilla extract', '1. In a large bowl, whisk together the flour, sugar, baking powder, and salt.\r\n2. In a separate bowl, whisk together the eggs, buttermilk, melted butter, and vanilla extract.\r\n3. Pour the wet ingredients into the dry ingredients and stir gently until just combined (do not overmix).\r\n4. Heat a lightly oiled griddle or non-stick frying pan over medium heat.\r\n5. Pour 1/4 cup of batter per pancake. Cook until surface bubbles pop, then flip and cook until golden brown.\r\n6. Serve warm with butter and pure maple syrup.', 'Breakfast', 'https://images.unsplash.com/photo-1533089860892-a7c6f0a88666?q=80&w=800&auto=format&fit=crop', '10 Min', '15 Min', '4', 'Easy', 1, '2026-09-06 05:55:16'),
(2, 'Sparkling Citrus Cooler', 'A refreshing blend of fresh orange, lime, and mint over ice — perfect for warm afternoons.', '1 cup fresh orange juice\r\n2 tbsp fresh lime juice\r\n1 tbsp honey or simple syrup\r\n1 cup sparkling water or club soda\r\n8 fresh mint leaves\r\nIce cubes\r\nOrange & lime slices for garnish', '1. In a measuring pitcher, combine fresh orange juice, lime juice, and honey until dissolved.\r\n2. Lightly crush the mint leaves in the bottom of two tall serving glasses.\r\n3. Fill both glasses generously with ice cubes.\r\n4. Divide the citrus blend evenly between glasses.\r\n5. Top with chilled sparkling water and stir gently.\r\n6. Garnish with fresh citrus wheels and mint sprigs before serving.', 'Drinks', 'https://images.unsplash.com/photo-1544145945-f90425340c7e?q=80&w=800&auto=format&fit=crop', '5 Min', '0 Min', '2', 'Easy', 2, '2026-09-06 05:55:16'),
(3, 'Vegetable Fried Rice', 'A quick, savory, and adaptable dish perfect for utilizing leftover rice and seasonal vegetables. High heat is essential for achieving the characteristic smoky flavor.', '3 cups cooked rice (chilled overnight)\r\n2 tbsp vegetable oil (divided)\r\n2 large eggs (lightly beaten)\r\n1 cup mixed vegetables (carrots, peas, sweet corn)\r\n2 cloves garlic (minced)\r\n3 tbsp low-sodium soy sauce\r\n1 tsp toasted sesame oil\r\n2 green onions (thinly sliced)', '1. Heat 1 tbsp of vegetable oil in a large wok or skillet over high heat. Pour in beaten eggs, scramble quickly, remove, and set aside.\r\n2. Add the remaining oil to the pan. Add minced garlic and mixed vegetables, stir-frying for 2-3 minutes until tender-crisp.\r\n3. Add the chilled rice, gently breaking up any clumps with a spatula. Stir-fry constantly for 4-5 minutes until the rice is heated through and slightly crispy.\r\n4. Drizzle soy sauce and sesame oil evenly over the rice, tossing thoroughly.\r\n5. Fold the scrambled eggs and sliced green onions back into the wok.\r\n6. Serve hot garnished with extra scallions.', 'Dinner', 'https://images.unsplash.com/photo-1512058564366-18510be2db19?q=80&w=1200&auto=format&fit=crop', '15 Min', '10 Min', '4', 'Easy', 1, '2026-09-06 05:55:16'),
(4, 'Salted Caramel Brownies', 'Fudgy dark chocolate brownies swirled with salted caramel and a flaky sea salt finish.', '200g dark chocolate (70% cocoa)\r\n150g unsalted butter\r\n1 cup granulated sugar\r\n3 large eggs\r\n1/2 cup all-purpose flour\r\n1/4 cup cocoa powder\r\n1/2 cup salted caramel sauce\r\n1 tsp flaky sea salt', '1. Preheat your oven to 180°C (350°F) and grease/line an 8-inch square baking tin.\r\n2. Melt dark chocolate and butter together in a heatproof bowl set over simmering water; cool slightly.\r\n3. In a bowl, whisk eggs and sugar until pale and fluffy (about 3 minutes).\r\n4. Gently fold melted chocolate into the egg mixture.\r\n5. Sift in flour and cocoa powder, folding with a spatula until just combined.\r\n6. Pour batter into the prepared tin. Spoon dollops of salted caramel across the top and swirl with a skewer.\r\n7. Bake for 25-30 minutes until set with a slight fudge center. Sprinkle with flaky sea salt and cool completely before slicing.', 'Desserts', 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?q=80&w=800&auto=format&fit=crop', '20 Min', '30 Min', '12', 'Medium', 3, '2026-09-06 05:55:16'),
(5, 'Overnight Oats with Figs', 'Creamy oats soaked overnight, layered with fresh figs, honey, and toasted walnuts.', '1 cup rolled oats\r\n1 cup unsweetened almond milk (or milk of choice)\r\n1/2 cup Greek yogurt\r\n1 tbsp chia seeds\r\n2 tbsp pure honey\r\n4 fresh ripe figs (sliced)\r\n2 tbsp walnuts (toasted and chopped)', '1. In a glass jar or medium bowl, mix rolled oats, almond milk, Greek yogurt, chia seeds, and 1 tbsp honey.\r\n2. Stir well to combine, cover tightly, and refrigerate for at least 6 hours or overnight.\r\n3. In the morning, give the oats a good stir (add a splash of milk if too thick).\r\n4. Spoon into serving bowls and arrange fresh fig slices on top.\r\n5. Scatter toasted chopped walnuts and drizzle with remaining honey before serving.', 'Breakfast', 'https://images.unsplash.com/photo-1517673132405-a56a62b18caf?q=80&w=800&auto=format&fit=crop', '10 Min', '0 Min', '2', 'Easy', 2, '2026-09-06 05:55:16'),
(6, 'Herb-Roasted Chicken Traybake', 'One-pan roast chicken with baby potatoes and seasonal vegetables in garlic herb butter.', '4 bone-in chicken thighs\r\n500g baby potatoes (halved)\r\n1 red onion (cut into wedges)\r\n1 cup cherry tomatoes\r\n3 tbsp olive oil\r\n3 cloves garlic (crushed)\r\n1 tbsp fresh rosemary (finely chopped)\r\n1 tbsp fresh thyme leaves\r\nSalt and freshly ground black pepper', '1. Preheat oven to 200°C (400°F).\r\n2. Place baby potatoes and red onion wedges on a large baking tray. Drizzle with 1 tbsp olive oil, salt, and black pepper.\r\n3. Arrange chicken thighs on the tray skin-side up.\r\n4. In a small bowl, mix remaining olive oil with garlic, rosemary, and thyme. Brush thoroughly over chicken skin and vegetables.\r\n5. Roast in oven for 25 minutes.\r\n6. Scatter cherry tomatoes over tray and return to oven for another 15 minutes until chicken skin is crisp and potatoes are tender.\r\n7. Rest 5 minutes before serving directly from tray.', 'Dinner', 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?q=80&w=800&auto=format&fit=crop', '15 Min', '40 Min', '4', 'Medium', 1, '2026-09-06 05:55:16'),
(7, 'Iced Matcha Latte', 'Whisked ceremonial matcha over oat milk and ice, lightly sweetened with maple syrup.', '1.5 tsp ceremonial grade matcha powder\r\n60 ml warm water (around 80°C / 175°F)\r\n1 cup barista-style oat milk\r\n1 tbsp pure maple syrup\r\n1 cup ice cubes', '1. Sift matcha powder into a small bowl to remove any clumps.\r\n2. Add warm water and whisk vigorously in a \"W\" motion using a bamboo chasen whisk until thick green foam appears.\r\n3. In a glass tumbler, add maple syrup and fill to the brim with ice cubes.\r\n4. Pour chilled oat milk over the ice until glass is 3/4 full.\r\n5. Slowly pour the whisked matcha on top to create a two-tone gradient effect.\r\n6. Stir well with a straw and enjoy immediately.', 'Drinks', 'https://images.unsplash.com/photo-1515823064-d6e0c04616a7?q=80&w=800&auto=format&fit=crop', '5 Min', '0 Min', '1', 'Easy', 2, '2026-09-06 05:55:16'),
(8, 'Mediterranean Chickpea Salad', 'Chickpeas, cucumber, and feta tossed in a lemon-oregano dressing with crusty bread.', '1 can (400g) chickpeas (rinsed and drained)\r\n1 English cucumber (diced)\r\n1 cup cherry tomatoes (halved)\r\n1/2 red onion (thinly sliced)\r\n1/3 cup pitted Kalamata olives\r\n100g Greek feta cheese (crumbled)\r\n3 tbsp extra virgin olive oil\r\n1.5 tbsp freshly squeezed lemon juice\r\n1 tsp dried oregano\r\nSalt and black pepper to taste', '1. In a large salad bowl, combine chickpeas, diced cucumber, cherry tomatoes, red onion, and Kalamata olives.\r\n2. In a small jar or shaker, combine olive oil, lemon juice, dried oregano, salt, and pepper. Shake vigorously.\r\n3. Pour the dressing over the vegetables and toss to coat evenly.\r\n4. Gently fold in the crumbled feta cheese.\r\n5. Chill for 10-15 minutes before serving with crusty sourdough bread.', 'Lunch', 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?q=80&w=800&auto=format&fit=crop', '15 Min', '0 Min', '3', 'Easy', 3, '2026-09-06 05:55:16'),
(9, 'Classic Tiramisu', 'Espresso-soaked ladyfingers layered with mascarpone cream and a dusting of cocoa.', '24 Italian ladyfingers (Savoiardi)\r\n250g mascarpone cheese (room temperature)\r\n3 large egg yolks\r\n1/2 cup granulated caster sugar\r\n1 cup heavy whipping cream\r\n1 cup strong espresso coffee (cooled)\r\n2 tbsp unsweetened Dutch-process cocoa powder', '1. Beat egg yolks and sugar together in a heatproof bowl over a pot of barely simmering water until pale and thick; allow to cool slightly.\r\n2. Gently fold mascarpone cheese into egg yolk mixture until smooth.\r\n3. In a separate bowl, whip heavy cream until medium-stiff peaks form, then gently fold into mascarpone cream.\r\n4. Pour cooled espresso into a shallow dish. Dip each ladyfinger quickly (1 second per side).\r\n5. Layer half the dipped ladyfingers in the bottom of an 8x8 inch dish.\r\n6. Spread half the cream over ladyfingers. Repeat with another layer of dipped biscuits and remaining cream.\r\n7. Cover and refrigerate for at least 4-6 hours (overnight preferred).\r\n8. Dust generously with cocoa powder right before serving.', 'Desserts', 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?q=80&w=800&auto=format&fit=crop', '25 Min', '0 Min', '8', 'Medium', 1, '2026-09-06 05:55:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'admin', 'admin@diaryoftaste.com', '$2y$10$imHUwaIucioGH7W9sGBpVOiRc5RTv7b0zcqNTjpSh35N8r3I7ym0C', '2026-09-06 05:55:16'),
(2, 'vishmi', 'vishmi@diaryoftaste.com', '$2y$10$imHUwaIucioGH7W9sGBpVOiRc5RTv7b0zcqNTjpSh35N8r3I7ym0C', '2026-09-06 05:55:16'),
(3, 'danuka', 'danuka@diaryoftaste.com', '$2y$10$imHUwaIucioGH7W9sGBpVOiRc5RTv7b0zcqNTjpSh35N8r3I7ym0C', '2026-09-06 05:55:16'),
(4, 'luffy', 'luffysilva@gmail.com', '$2y$10$6Mo.F.ybYn61vhtAHszK8..y.VVWFOoSr1UDAQh2erZ.Q.ZtP7tlm', '2026-09-06 06:21:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recipes_category` (`category`),
  ADD KEY `idx_recipes_user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `fk_recipes_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
