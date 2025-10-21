const { createElement } = wp.element;
const { Button } = wp.components;

const container = document.getElementById('root');

if (container) {
    const root = ReactDOM.createRoot(container);

    function App() {
        return (
            <div id="root">
                <div className="root-card">
                    <h1 className="root-heading">Hello, my life!</h1>
                    <Button
                        className="root-button"
                        onClick={() => alert('Button clicked!')}
                    >
                        Click Me!
                    </Button>
                </div>
            </div>
        );
    }

    root.render(createElement(App));
}
