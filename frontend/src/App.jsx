import { Routes, Route } from "react-router-dom";
import './App.css'
import Home from '@/pages/Home'
import Timer from '@/components/Timer'


function App() {

  return (
    <>
      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/timer" element={<Timer />} />
      </Routes>
    </>
  )
}

export default App
