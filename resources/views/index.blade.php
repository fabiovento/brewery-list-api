
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Elenco birrifici</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    />
</head>
<body>
<div id="root"></div>
<script src="https://unpkg.com/react@18/umd/react.production.min.js" crossorigin></script>
<script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js" crossorigin></script>
<script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
<script type="text/babel">
    const API_URL = 'http://localhost/api';
    function App() {
        const [token, setToken] = React.useState(localStorage.getItem('token'));
        const [data, setData] = React.useState([]);
        const [error, setError] = React.useState(null);
        const [page, setPage] = React.useState(1);
        const [loading, setLoading] = React.useState(false);
        React.useEffect(() => { if (token) validateToken(token); }, []);
        const validateToken = async (tk) => {
            try {
                const res = await fetch(`${API_URL}/list?page=1`, {
                    headers: { 'Authorization': `Bearer ${tk}` }
                });
                if (res.status === 200) {
                    const json = await res.json(); setData(json.data);
                } else {
                    localStorage.removeItem('token'); setToken(null);
                }
            } catch {
                localStorage.removeItem('token'); setToken(null);
            }
        };
        const handleLogin = async (e) => {
            e.preventDefault();
            const email = e.target.email.value;
            const password = e.target.password.value;
            try {
                const res = await fetch(`${API_URL}/login`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });
                if (res.status === 401) {
                    setError('Dati di accesso non corretti');
                } else {
                    const json = await res.json();
                    localStorage.setItem('token', json.access_token);
                    setError(null); setToken(json.access_token);
                    validateToken(json.access_token);
                }
            } catch {
                setError('Errore di rete');
            }
        };
        const handleLogout = () => {
            localStorage.removeItem('token');
            setToken(null); setData([]); setPage(1);
        };
        const handlePageChange = async (newPage) => {
            setLoading(true);
            try {
                const res = await fetch(`${API_URL}/list?page=${newPage}`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                if (res.status === 200) {
                    const json = await res.json();
                    setData(json.data); setPage(newPage);
                } else {
                    handleLogout();
                }
            } catch {
                handleLogout();
            }
            setLoading(false);
        };
        return (
            <div className="container py-4">
                {token ? (
                    <>
                        <div className="d-flex justify-content-between align-items-center mb-3">
                            <h2>Lista Elementi</h2>
                            <button className="btn btn-danger" onClick={handleLogout}>Logout</button>
                        </div>
                        {loading ? (
                            <p>Caricamento...</p>
                        ) : (
                            <div className="table-responsive">
                                <table className="table table-striped">
                                    <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Tipo birrificio</th>
                                        <th>Indirizzo 1</th>
                                        <th>Indirizzo 2</th>
                                        <th>Indirizzo 3</th>
                                        <th>Città</th>
                                        <th>Stato/Provincia</th>
                                        <th>Codice postale</th>
                                        <th>Paese</th>
                                        <th>Longitudine</th>
                                        <th>Latitudine</th>
                                        <th>Telefono</th>
                                        <th>Sito web</th>
                                        <th>Stato</th>
                                        <th>Via</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {data.map(item => (
                                        <tr key={item.id}>
                                            <td>{item.name}</td>
                                            <td>{item.brewery_type}</td>
                                            <td>{item.address_1}</td>
                                            <td>{item.address_2}</td>
                                            <td>{item.address_3}</td>
                                            <td>{item.city}</td>
                                            <td>{item.state_province}</td>
                                            <td>{item.postal_code}</td>
                                            <td>{item.country}</td>
                                            <td>{item.longitude}</td>
                                            <td>{item.latitude}</td>
                                            <td>{item.phone}</td>
                                            <td><a href={item.website_url} target="_blank" rel="noreferrer">{item.website_url}</a></td>
                                            <td>{item.state}</td>
                                            <td>{item.street}</td>
                                        </tr>
                                    ))}
                                    </tbody>
                                </table>
                                <nav>
                                    <ul className="pagination">
                                        {Array.from({ length: 10 }, (_, i) => i + 1).map(n => (
                                            <li className={`page-item ${page === n ? 'active' : ''}`} key={n}>
                                                <button className="page-link" onClick={() => handlePageChange(n)}>{n}</button>
                                            </li>
                                        ))}
                                    </ul>
                                </nav>
                            </div>
                        )}
                    </>
                ) : (
                    <div className="col-md-6 offset-md-3">
                        <h2>Login</h2>
                        {error && <div className="alert alert-danger">{error}</div>}
                        <form onSubmit={handleLogin}>
                            <div className="mb-3">
                                <label className="form-label">Email</label>
                                <input type="email" name="email" className="form-control" required />
                            </div>
                            <div className="mb-3">
                                <label className="form-label">Password</label>
                                <input type="password" name="password" className="form-control" required />
                            </div>
                            <button type="submit" className="btn btn-primary">Login</button>
                        </form>
                    </div>
                )}
            </div>
        );
    }
    const root = ReactDOM.createRoot(document.getElementById('root'));
    root.render(<App />);
</script>
</body>
</html>
