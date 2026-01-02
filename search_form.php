<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="author" content="Anish Pangeni">
    <title>Search Players - UK E-Sports League</title>
    <link rel="stylesheet" href="style.css?v=4.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* [Previous CSS remains largely the same, ensuring UI consistency] */
        .modal-backdrop {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(8px);
            display: none; justify-content: center; align-items: center;
            z-index: 1000; opacity: 0; transition: opacity 0.3s ease;
        }
        .modal-backdrop.active { display: flex; opacity: 1; }
        .modal-content {
            background: white; width: 90%; max-width: 800px; max-height: 90vh;
            overflow-y: auto; border-radius: 1.5rem; position: relative;
            transform: translateY(20px); transition: transform 0.3s ease;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        .modal-backdrop.active .modal-content { transform: translateY(0); }
        .close-modal {
            position: absolute; top: 1rem; right: 1rem; background: white;
            border: none; width: 40px; height: 40px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; z-index: 10; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .close-modal:hover { transform: rotate(90deg); background: #F1F5F9; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="index.html" class="logo">
                <i class="fas fa-gamepad"></i>
                <span>UK E-Sports League</span>
            </a>
            <ul class="nav-links">
                <li><a href="index.html">Home</a></li>
                <li><a href="search_form.php" class="active">Search</a></li>
                <li><a href="register_form.html">Merchandise</a></li>
                <li><a href="admin_login.html">Admin</a></li>
            </ul>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="container" style="padding: 4rem 0 2rem;">
        <div class="section-title">
            <h2><i class="fas fa-search"></i> Player Search</h2>
            <p>Find players by name, email, or team affiliation</p>
            <p style="margin-top: 1rem; font-size: 0.95rem;">
                Not listed? <a href="register.php" style="color: #2563EB; font-weight: 600; text-decoration: none;">Join the Tournament <i class="fas fa-arrow-right"></i></a>
            </p>
        </div>
    </section>

    <!-- PHP Data Fetching -->
    <?php
    require_once 'dbconnect.php';
    
    // Fetch all participants
    $sql = "SELECT * FROM participant";
    $result = $conn->query($sql);
    
    $players = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Assign a random team if none exists (for demo purposes)
            $row['team_id'] = $row['team_id'] ?? rand(1, 4);
            
            // Map team IDs to names
            $teamNames = [
                1 => 'NovaCore',
                2 => 'IronWolves',
                3 => 'PulseForge',
                4 => 'ShadowRift'
            ];
            $row['team_name'] = $teamNames[$row['team_id']] ?? 'Free Agent';
            
            // Ensure stats are numeric
            $row['kills'] = (int)$row['kills'];
            $row['deaths'] = (int)$row['deaths'];
            
            $players[] = $row;
        }
    }
    ?>

    <!-- Search Form -->
    <section class="container" style="padding-bottom: 3rem;">
        <div style="background: white; padding: 2rem; border-radius: 1.5rem; box-shadow: 0 10px 15px -3px rgba(30, 64, 175, 0.1); border: 1px solid rgba(59, 130, 246, 0.1);">
            <form id="searchForm" onsubmit="searchPlayers(event)">
                <!-- Search Input Row -->
                <div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
                    <div style="flex: 1; position: relative;">
                        <i class="fas fa-search" style="position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 1.1rem;"></i>
                        <input type="text" id="searchTerm" placeholder="Search players by name, ID, or email..." 
                            style="width: 100%; padding: 1rem 1rem 1rem 3.5rem; border: 2px solid #E2E8F0; border-radius: 1rem; font-size: 1.1rem; transition: all 0.25s; font-family: 'Inter', sans-serif; outline: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);"
                            onfocus="this.style.borderColor='#3B82F6'; this.style.boxShadow='0 0 0 4px rgba(59, 130, 246, 0.1)';"
                            onblur="this.style.borderColor='#E2E8F0'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.05)';"
                            oninput="searchPlayers(event)">
                    </div>
                    <button type="submit" class="btn btn-primary" style="padding: 0 2rem; font-size: 1.1rem;">
                        Search
                    </button>
                </div>

                <!-- Team Filters -->
                <div>
                    <label style="display: block; color: #64748B; font-weight: 600; margin-bottom: 1rem; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        Filter by Team
                    </label>
                    <input type="hidden" id="teamFilter" value="">
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                        <!-- All Teams -->
                        <div onclick="selectTeam('')" class="team-filter-card active" id="filter-all"
                            style="cursor: pointer; padding: 1rem; border: 2px solid #eff6ff; border-radius: 1rem; background: white; transition: all 0.2s; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                            <div style="width: 40px; height: 40px; background: #E2E8F0; border-radius: 50%; margin: 0 auto 0.75rem; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-users" style="color: #64748B;"></i>
                            </div>
                            <h4 style="margin: 0; color: #1E293B; font-weight: 700;">All Teams</h4>
                        </div>
                        
                        <!-- Teams List -->
                        <div onclick="selectTeam('1')" class="team-filter-card" id="filter-1" style="cursor: pointer; padding: 1rem; border: 2px solid transparent; border-radius: 1rem; background: #F0F9FF; text-align: center;">
                             <div style="width: 40px; height: 40px; background: #3B82F6; border-radius: 50%; margin: 0 auto 0.75rem; display: flex; align-items: center; justify-content: center; color: white;">N</div>
                             <h4 style="margin: 0; color: #0C4A6E; font-weight: 700;">NovaCore</h4>
                        </div>
                        
                        <div onclick="selectTeam('2')" class="team-filter-card" id="filter-2" style="cursor: pointer; padding: 1rem; border: 2px solid transparent; border-radius: 1rem; background: #FEF2F2; text-align: center;">
                             <div style="width: 40px; height: 40px; background: #EF4444; border-radius: 50%; margin: 0 auto 0.75rem; display: flex; align-items: center; justify-content: center; color: white;">I</div>
                             <h4 style="margin: 0; color: #7F1D1D; font-weight: 700;">IronWolves</h4>
                        </div>
                        
                        <div onclick="selectTeam('3')" class="team-filter-card" id="filter-3" style="cursor: pointer; padding: 1rem; border: 2px solid transparent; border-radius: 1rem; background: #ECFDF5; text-align: center;">
                             <div style="width: 40px; height: 40px; background: #10B981; border-radius: 50%; margin: 0 auto 0.75rem; display: flex; align-items: center; justify-content: center; color: white;">P</div>
                             <h4 style="margin: 0; color: #064E3B; font-weight: 700;">PulseForge</h4>
                        </div>
                        
                        <div onclick="selectTeam('4')" class="team-filter-card" id="filter-4" style="cursor: pointer; padding: 1rem; border: 2px solid transparent; border-radius: 1rem; background: #F5F3FF; text-align: center;">
                             <div style="width: 40px; height: 40px; background: #8B5CF6; border-radius: 50%; margin: 0 auto 0.75rem; display: flex; align-items: center; justify-content: center; color: white;">S</div>
                             <h4 style="margin: 0; color: #4C1D95; font-weight: 700;">ShadowRift</h4>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Results Container -->
    <section class="container" style="padding-bottom: 4rem;">
        <div id="resultsContainer"></div>
    </section>

    <!-- Player Profile Modal -->
    <div id="profileModal" class="modal-backdrop" onclick="closeModal(event)">
        <div class="modal-content">
            <button class="close-modal" onclick="closeModal(event)">
                <i class="fas fa-times"></i>
            </button>
            <div id="modalBody">
                <!-- Content injected via JS -->
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3>Student Information</h3>
                    <p><strong>Name:</strong> Anish Pangeni</p>
                    <p><strong>Email:</strong> bwit24d.anh@ismt.edu.np</p>
                    <p><strong>Student ID:</strong> bi95sn</p>
                </div>
                <!-- Other footer sections... -->
            </div>
            <div class="footer-bottom">
                <p class="copyright">&copy; 2026 UK E-Sports League. Created by Anish Pangeni.</p>
            </div>
        </div>
    </footer>

    <script>
        // Pass PHP data to JavaScript
        const dbPlayers = <?php echo json_encode($players); ?>;
        
        // Enrich player data with logic (same as before but using DB stats)
        const enrichedPlayers = dbPlayers.map(p => {
            // Stats logic
            const matches = Math.floor(Math.random() * 50) + 10;
            const wins = Math.floor(matches * (0.4 + Math.random() * 0.4));
            const winRate = p.deaths > 0 ? ((p.kills / (p.kills + p.deaths)) * 100).toFixed(0) : 50;
            
            // Default 0 stats for new players
            const kd = p.deaths > 0 ? (p.kills / p.deaths).toFixed(2) : p.kills.toFixed(2);
            
            // Random attributes for flavor
            const seed = p.id;
            const outcomes = [];
            for(let i=0; i<3; i++) {
                const isWin = (seed + i) % 2 === 0;
                outcomes.push({
                    result: isWin ? 'WIN' : 'LOSS',
                    score: isWin ? `13-${7 + (seed % 5)}` : `${8 + (seed % 5)}-13`,
                    map: ['Dust II', 'Mirage', 'Inferno', 'Nuke', 'Overpass'][ (seed + i) % 5 ]
                });
            }

            return {
                ...p,
                kd: kd,
                matches: matches,
                wins: wins,
                winRate: winRate,
                rank: ['Bronze', 'Silver', 'Gold', 'Platinum', 'Diamond', 'Master', 'Grandmaster'][p.id % 7],
                role: ['Entry Fragger', 'Support', 'AWPer', 'IGL', 'Lurker'][p.id % 5],
                recentMatches: outcomes,
                topKills: p.kills > 0 ? Math.floor(p.kills / 10) + 10 : 0, 
                headshotPercentage: Math.floor(Math.random() * 40) + 20,
                favWeapon: ['AK-47', 'M4A4', 'AWP', 'Desert Eagle', 'USP-S'][p.id % 5],
                playStyle: ['Aggressive', 'Passive', 'Tactical', 'Unpredictable'][p.id % 4]
            };
        });

        // Search and Display Logic
        function selectTeam(teamId) {
            document.getElementById('teamFilter').value = teamId;
            document.querySelectorAll('.team-filter-card').forEach(card => {
                card.style.borderColor = 'transparent';
                card.style.transform = 'scale(1)';
                card.style.boxShadow = 'none';
                if (card.id === 'filter-all' && teamId !== '') card.style.borderColor = '#eff6ff';
            });
            const activeId = teamId === '' ? 'filter-all' : `filter-${teamId}`;
            const activeCard = document.getElementById(activeId);
            if (activeCard) {
                activeCard.style.borderColor = '#3B82F6';
                activeCard.style.transform = 'translateY(-2px)';
                activeCard.style.boxShadow = '0 10px 15px -3px rgba(59, 130, 246, 0.2)';
            }
            searchPlayers(new Event('submit'));
        }

        function searchPlayers(event) {
            if (event) event.preventDefault();
            const searchTerm = document.getElementById('searchTerm').value.toLowerCase();
            const teamFilter = document.getElementById('teamFilter').value;
            
            let results = enrichedPlayers;
            
            if (searchTerm) {
                results = results.filter(player => 
                    player.firstname.toLowerCase().includes(searchTerm) ||
                    player.surname.toLowerCase().includes(searchTerm) ||
                    player.email.toLowerCase().includes(searchTerm) ||
                    player.id.toString().includes(searchTerm)
                );
            }
            
            if (teamFilter) {
                results = results.filter(player => player.team_id == teamFilter);
            }
            
            displayResults(results);
        }

        function displayResults(results) {
            const container = document.getElementById('resultsContainer');
            if (results.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 4rem 2rem; color: #6B7684; background: white; border-radius: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                        <i class="fas fa-ghost" style="font-size: 3rem; color: #E2E8F0; margin-bottom: 1rem;"></i>
                        <p style="font-size: 1.1rem;">No agents found matching your criteria.</p>
                    </div>`;
                return;
            }

            container.innerHTML = `
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 1.5rem;">
                    ${results.map(player => createDetailedCard(player)).join('')}
                </div>
            `;
        }

        function createDetailedCard(player) {
            const teamColors = {
                1: { bg: '#F0F9FF', text: '#0C4A6E', border: '#e0f2fe' },
                2: { bg: '#FEF2F2', text: '#7F1D1D', border: '#fee2e2' },
                3: { bg: '#ECFDF5', text: '#064E3B', border: '#d1fae5' },
                4: { bg: '#F5F3FF', text: '#4C1D95', border: '#ede9fe' }
            };
            const theme = teamColors[player.team_id] || teamColors[1];
            
            let badge = '';
            if (player.topKills > 50) badge = `<span style="background: #FEF3C7; color: #D97706; padding: 2px 8px; border-radius: 99px; font-size: 0.7rem; font-weight: 700; margin-left: auto;">🔥 TOP FRAGGER</span>`;

            return `
                <div onclick="openProfile(${player.id})" style="background: white; border-radius: 1.25rem; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); transition: all 0.3s; cursor: pointer; position: relative; border: 1px solid #F1F5F9;"
                    onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 20px 25px -5px rgba(0, 0, 0, 0.1)'"
                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.05)'">
                    
                    <div style="background: ${theme.bg}; padding: 1.25rem; border-bottom: 1px solid ${theme.border}; display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 48px; height: 48px; background: white; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                            ${player.team_name ? player.team_name[0] : 'P'}
                        </div>
                        <div style="flex: 1;">
                            <h4 style="margin: 0; color: ${theme.text}; font-size: 1rem; font-weight: 700;">${player.team_name}</h4>
                            <span style="font-size: 0.75rem; opacity: 0.8; color: ${theme.text};">${player.role}</span>
                        </div>
                        ${badge}
                    </div>

                    <div style="padding: 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                            <div style="width: 64px; height: 64px; background: #F8FAFC; border-radius: 50%; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 2px solid #E2E8F0;">
                                <img src="https://ui-avatars.com/api/?name=${player.firstname}+${player.surname}&background=random&size=128" alt="${player.firstname}" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <div>
                                <h3 style="margin: 0; font-size: 1.1rem; color: #1E293B; font-weight: 700;">${player.firstname} ${player.surname}</h3>
                                <span style="color: #64748B; font-size: 0.875rem;">Rank: <span style="color: #3B82F6; font-weight: 600;">${player.rank}</span></span>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; background: #F8FAFC; padding: 1rem; border-radius: 1rem;">
                            <div style="text-align: center;">
                                <div style="font-size: 0.75rem; color: #64748B; text-transform: uppercase; font-weight: 600;">K/D Ratio</div>
                                <div style="font-size: 1.25rem; font-weight: 800; color: ${player.kd >= 1.5 ? '#10B981' : '#1E293B'};">${player.kd}</div>
                            </div>
                            <div style="text-align: center; border-left: 1px solid #E2E8F0;">
                                <div style="font-size: 0.75rem; color: #64748B; text-transform: uppercase; font-weight: 600;">Win Rate</div>
                                <div style="font-size: 1.25rem; font-weight: 800; color: #3B82F6;">${player.winRate}%</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Modal functions remain same as before but using passed data...
        function openProfile(playerId) {
            const player = enrichedPlayers.find(p => p.id === parseInt(playerId));
            if (!player) return;

            const modal = document.getElementById('profileModal');
            const body = document.getElementById('modalBody');
            
            // [Modal HTML Generation - same complexity as before]
            body.innerHTML = `
                <div style="position: relative;">
                    <button onclick="closeModal(event)" style="position:absolute; right: 1rem; top: 1rem; z-index: 50; background:white; padding: 0.5rem; border-radius: 50%; border:none; cursor: pointer;">✕</button>
                    <div style="height: 160px; background: linear-gradient(135deg, #3B82F6, #1E40AF); border-radius: 1.5rem 1.5rem 0 0;"></div>
                    <div style="padding: 0 2rem 2rem; margin-top: -60px;">
                        <img src="https://ui-avatars.com/api/?name=${player.firstname}+${player.surname}&background=0F172A&color=fff&size=256" style="width: 120px; height: 120px; border-radius: 1.5rem; border: 4px solid white; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);">
                        <h2 style="margin: 1rem 0 0; font-size: 2rem; color: #1E293B;">${player.firstname} ${player.surname}</h2>
                        <p style="color: #64748B; margin-bottom: 2rem;">${player.team_name} • ${player.role}</p>
                        
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 2rem;">
                            <div style="padding: 1.5rem; background: #F8FAFC; border-radius: 1rem; text-align: center;">
                                <div style="color: #64748B; font-size: 0.8rem; font-weight: 600;">KILLS</div>
                                <div style="font-size: 1.75rem; font-weight: 800; color: #10B981;">${player.kills}</div>
                            </div>
                            <div style="padding: 1.5rem; background: #F8FAFC; border-radius: 1rem; text-align: center;">
                                <div style="color: #64748B; font-size: 0.8rem; font-weight: 600;">DEATHS</div>
                                <div style="font-size: 1.75rem; font-weight: 800; color: #EF4444;">${player.deaths}</div>
                            </div>
                            <div style="padding: 1.5rem; background: #F8FAFC; border-radius: 1rem; text-align: center;">
                                <div style="color: #64748B; font-size: 0.8rem; font-weight: 600;">MATCHES</div>
                                <div style="font-size: 1.75rem; font-weight: 800; color: #3B82F6;">${player.matches}</div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            modal.classList.add('active');
        }

        function closeModal(event) {
             if (event.target.classList.contains('modal-backdrop') || event.target.closest('.close-modal') || event.target.tagName === 'BUTTON') {
                document.getElementById('profileModal').classList.remove('active');
            }
        }
        
        // Initial render
        displayResults(enrichedPlayers);
    </script>
</body>
</html>